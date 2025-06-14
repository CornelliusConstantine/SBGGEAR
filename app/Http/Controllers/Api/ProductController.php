<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        try {
            \Log::info('ProductController: Fetching products with filters: ' . json_encode($request->all()));
            
            $query = Product::with('category')
                ->when($request->filled('category'), function ($query) use ($request) {
                    \Log::info('ProductController: Filtering by category slug: ' . $request->category);
                    $query->whereHas('category', function ($q) use ($request) {
                        $q->where('slug', $request->category);
                    });
                })
                ->when($request->filled('categories'), function ($query) use ($request) {
                    \Log::info('ProductController: Filtering by multiple category IDs: ' . $request->categories);
                    $categoryIds = explode(',', $request->categories);
                    \Log::info('ProductController: Parsed category IDs: ' . implode(', ', $categoryIds));
                    
                    // Convert string IDs to integers to ensure proper comparison
                    $categoryIds = array_map('intval', $categoryIds);
                    
                    $query->whereHas('category', function ($q) use ($categoryIds) {
                        $q->whereIn('id', $categoryIds);
                    });
                })
                ->when($request->filled('min_price'), function ($query) use ($request) {
                    $query->where('price', '>=', $request->min_price);
                })
                ->when($request->filled('max_price'), function ($query) use ($request) {
                    $query->where('price', '<=', $request->max_price);
                })
                ->when($request->filled('featured'), function ($query) use ($request) {
                    $featured = filter_var($request->featured, FILTER_VALIDATE_BOOLEAN);
                    $query->where('is_featured', $featured);
                });
                
            // Handle sorting properly
            if ($request->filled('sort')) {
                $sortField = $request->sort;
                $sortDirection = $request->filled('direction') ? $request->direction : 'asc';
                
                // Handle special sort cases
                if ($sortField === 'price_asc') {
                    $query->orderBy('price', 'asc');
                } else if ($sortField === 'price_desc') {
                    $query->orderBy('price', 'desc');
                } else if ($sortField === 'newest') {
                    $query->orderBy('created_at', 'desc');
                } else {
                    // Make sure the sort field is a valid column
                    $validColumns = ['id', 'name', 'price', 'stock', 'created_at', 'updated_at'];
                    if (in_array($sortField, $validColumns)) {
                        $query->orderBy($sortField, $sortDirection);
                    } else {
                        // Default sorting
                        $query->orderBy('created_at', 'desc');
                    }
                }
            } else {
                // Default sorting
                $query->orderBy('created_at', 'desc');
            }

            $perPage = $request->input('limit', 12);
            $products = $query->paginate($perPage);
            
            \Log::info('ProductController: Found ' . $products->count() . ' products');

            return response()->json([
                'data' => $products->items(),
                'meta' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total()
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('ProductController: Error fetching products: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error fetching products: ' . $e->getMessage(),
                'success' => false
            ], 500);
        }
    }

    public function show(Product $product)
    {
        try {
            $product->load(['category', 'comments']);
            
            // Ensure images are properly formatted
            if (is_string($product->images)) {
                $product->images = json_decode($product->images, true);
            }
            
            // Ensure specifications are properly formatted
            if (is_string($product->specifications)) {
                $product->specifications = json_decode($product->specifications, true);
            }
            
            return response()->json([
                'data' => $product,
                'success' => true
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error loading product details: ' . $e->getMessage(),
                'success' => false
            ], 500);
        }
    }

    public function search(Request $request, $query = null)
    {
        try {
            // Decode URL-encoded query
            $query = urldecode($query);
            \Log::info('ProductController: Searching products with query: ' . $query);
            
            if (empty($query)) {
                return response()->json([
                    'message' => 'Search query is required',
                    'success' => false
                ], 400);
            }
            
            // Clean up the query
            $searchTerm = trim($query);
            
            // Log the cleaned search term
            \Log::info('ProductController: Cleaned search term: ' . $searchTerm);
            
            $products = Product::with('category')
                ->where(function($q) use ($searchTerm) {
                    $q->where('name', 'ilike', '%' . $searchTerm . '%')
                      ->orWhere('description', 'ilike', '%' . $searchTerm . '%')
                      ->orWhereHas('category', function ($subq) use ($searchTerm) {
                          $subq->where('name', 'ilike', '%' . $searchTerm . '%');
                      });
                })
                ->paginate($request->input('per_page', 12));
                
            \Log::info('ProductController: Found ' . $products->count() . ' products matching query: ' . $searchTerm);
            
            return response()->json([
                'data' => $products->items(),
                'meta' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total()
                ],
                'success' => true
            ]);
        } catch (\Exception $e) {
            \Log::error('ProductController: Error searching products: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error searching products: ' . $e->getMessage(),
                'success' => false
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'specifications' => ['nullable', 'string'], // Accept as string that will be JSON parsed
            'weight' => ['required', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'max:5120'], // 5MB max
            'additional_images.*' => ['nullable', 'image', 'max:5120'], // 5MB max
            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
        ]);

        $images = [];
        
        // Process main image
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = Str::uuid() . '.' . $image->getClientOriginalExtension();
            
            // Create thumbnail using native PHP GD
            $thumbnail = $this->createThumbnail($image->getRealPath(), 300, 300);
            
            // Store original and thumbnail
            Storage::disk('public')->put('products/original/' . $filename, file_get_contents($image));
            Storage::disk('public')->put('products/thumbnails/' . $filename, $thumbnail);

            $images['main'] = $filename;
        }
        
        // Process additional images
        if ($request->hasFile('additional_images')) {
            $images['gallery'] = [];
            foreach ($request->file('additional_images') as $image) {
                $filename = Str::uuid() . '.' . $image->getClientOriginalExtension();
                
                // Create thumbnail using native PHP GD
                $thumbnail = $this->createThumbnail($image->getRealPath(), 300, 300);

                // Store original and thumbnail
                Storage::disk('public')->put('products/original/' . $filename, file_get_contents($image));
                Storage::disk('public')->put('products/thumbnails/' . $filename, $thumbnail);

                $images['gallery'][] = $filename;
            }
        }

        // Process specifications
        $specifications = [];
        if ($request->has('specifications')) {
            if (is_string($request->specifications)) {
                try {
                    $specifications = json_decode($request->specifications, true) ?: [];
                } catch (\Exception $e) {
                    $specifications = [];
                }
            } else {
                $specifications = $request->specifications;
            }
        }

        // Process boolean fields
        $isActive = $request->has('is_active') ? filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN) : true;
        $isFeatured = $request->has('is_featured') ? filter_var($request->is_featured, FILTER_VALIDATE_BOOLEAN) : false;

        try {
            $product = Product::create([
                'category_id' => $request->category_id,
                'name' => $request->name,
                'slug' => Str::slug($request->name), // Model boot method will ensure uniqueness
                'description' => $request->description,
                'price' => $request->price,
                'stock' => $request->stock,
                'sku' => $request->sku ?? $this->generateSku($request->name),
                'specifications' => $specifications,
                'images' => $images,
                'weight' => $request->weight,
                'is_active' => $isActive,
                'is_featured' => $isFeatured,
            ]);

            return response()->json([
                'message' => 'Product created successfully',
                'product' => $product,
                'success' => true
            ], 201);
        } catch (\Exception $e) {
            // Delete uploaded images if product creation fails
            if (!empty($images)) {
                if (isset($images['main'])) {
                    Storage::disk('public')->delete('products/original/' . $images['main']);
                    Storage::disk('public')->delete('products/thumbnails/' . $images['main']);
                }
                
                if (isset($images['gallery']) && is_array($images['gallery'])) {
                    foreach ($images['gallery'] as $image) {
                        Storage::disk('public')->delete('products/original/' . $image);
                        Storage::disk('public')->delete('products/thumbnails/' . $image);
                    }
                }
            }
            
            return response()->json([
                'message' => 'Failed to create product: ' . $e->getMessage(),
                'success' => false
            ], 500);
        }
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'specifications' => ['nullable'],
            'weight' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'max:5120'], // 5MB max
            'additional_images.*' => ['nullable', 'image', 'max:5120'], // 5MB max
        ]);

        // Process specifications
        $specifications = [];
        if ($request->has('specifications')) {
            if (is_string($request->specifications)) {
                try {
                    $specifications = json_decode($request->specifications, true) ?: [];
                } catch (\Exception $e) {
                    $specifications = [];
                }
            } else {
                $specifications = $request->specifications;
            }
        }

        // Process boolean fields
        $isActive = $request->has('is_active') ? filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN) : $product->is_active;
        $isFeatured = $request->has('is_featured') ? filter_var($request->is_featured, FILTER_VALIDATE_BOOLEAN) : $product->is_featured;

        try {
            // Update product data
            $product->update([
                'category_id' => $request->category_id,
                'name' => $request->name,
                'slug' => Str::slug($request->name), // Model boot method will ensure uniqueness
                'description' => $request->description,
                'price' => $request->price,
                'specifications' => $specifications,
                'weight' => $request->weight,
                'is_active' => $isActive,
                'is_featured' => $isFeatured,
                'sku' => $request->sku ?? $product->sku,
            ]);

            // Process main image
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $filename = Str::uuid() . '.' . $image->getClientOriginalExtension();
                
                // Create thumbnail using native PHP GD
                $thumbnail = $this->createThumbnail($image->getRealPath(), 300, 300);
                
                // Store original and thumbnail
                Storage::disk('public')->put('products/original/' . $filename, file_get_contents($image));
                Storage::disk('public')->put('products/thumbnails/' . $filename, $thumbnail);

                // Update images array
                $images = $product->images ?? [];
                if (is_string($images)) {
                    try {
                        $images = json_decode($images, true) ?: [];
                    } catch (\Exception $e) {
                        $images = [];
                    }
                }
                
                // Delete old main image if exists
                if (isset($images['main'])) {
                    Storage::disk('public')->delete('products/original/' . $images['main']);
                    Storage::disk('public')->delete('products/thumbnails/' . $images['main']);
                }
                
                $images['main'] = $filename;
                $product->update(['images' => $images]);
            }
            
            // Process additional images
            if ($request->hasFile('additional_images')) {
                $images = $product->images ?? [];
                if (is_string($images)) {
                    try {
                        $images = json_decode($images, true) ?: [];
                    } catch (\Exception $e) {
                        $images = [];
                    }
                }
                
                if (!isset($images['gallery'])) {
                    $images['gallery'] = [];
                }
                
                foreach ($request->file('additional_images') as $image) {
                    $filename = Str::uuid() . '.' . $image->getClientOriginalExtension();
                    
                    // Create thumbnail using native PHP GD
                    $thumbnail = $this->createThumbnail($image->getRealPath(), 300, 300);

                    // Store original and thumbnail
                    Storage::disk('public')->put('products/original/' . $filename, file_get_contents($image));
                    Storage::disk('public')->put('products/thumbnails/' . $filename, $thumbnail);

                    $images['gallery'][] = $filename;
                }
                
                $product->update(['images' => $images]);
            }

            return response()->json([
                'message' => 'Product updated successfully',
                'product' => $product->fresh(),
                'success' => true
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update product: ' . $e->getMessage(),
                'success' => false
            ], 500);
        }
    }

    public function uploadImages(Request $request, Product $product)
    {
        $request->validate([
            'images.*' => ['required', 'image', 'max:5120'], // 5MB max
        ]);

        if (!$request->hasFile('images')) {
            return response()->json([
                'message' => 'No images provided',
                'success' => false
            ], 400);
        }

        $images = $product->images ?? [];
        if (is_string($images)) {
            try {
                $images = json_decode($images, true) ?: [];
            } catch (\Exception $e) {
                $images = [];
            }
        }

        if (!isset($images['gallery'])) {
            $images['gallery'] = [];
        }

        foreach ($request->file('images') as $image) {
            $filename = Str::uuid() . '.' . $image->getClientOriginalExtension();
            
            // Create thumbnail using native PHP GD
            $thumbnail = $this->createThumbnail($image->getRealPath(), 300, 300);
            
            // Store original and thumbnail
            Storage::disk('public')->put('products/original/' . $filename, file_get_contents($image));
            Storage::disk('public')->put('products/thumbnails/' . $filename, $thumbnail);
            
            // Add to gallery
            $images['gallery'][] = $filename;
        }

        $product->update(['images' => $images]);

        // Return updated product with image URLs
        $product->refresh();
        
        return response()->json([
            'message' => 'Images uploaded successfully',
            'product' => $product,
            'image_url' => $product->image_url,
            'gallery_urls' => $product->gallery_urls,
            'success' => true
        ]);
    }

    public function destroy(Product $product)
    {
        try {
            // Get product ID before deletion for confirmation
            $productId = $product->id;
            $productName = $product->name;
            
            // Delete product images
            if (!empty($product->images)) {
                $images = $product->images;
                
                // Handle string format if needed
                if (is_string($images)) {
                    $images = json_decode($images, true);
                }
                
                // Delete main image if exists
                if (isset($images['main'])) {
                    Storage::disk('public')->delete('products/original/' . $images['main']);
                    Storage::disk('public')->delete('products/thumbnails/' . $images['main']);
                }
                
                // Delete gallery images if exist
                if (isset($images['gallery']) && is_array($images['gallery'])) {
                    foreach ($images['gallery'] as $image) {
                        Storage::disk('public')->delete('products/original/' . $image);
                        Storage::disk('public')->delete('products/thumbnails/' . $image);
                    }
                }
            }

            // Force delete to ensure it's removed from database
            $product->forceDelete();

            return response()->json([
                'message' => "Product '$productName' (ID: $productId) deleted successfully",
                'success' => true
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete product: ' . $e->getMessage(),
                'success' => false
            ], 500);
        }
    }

    private function generateSku($name)
    {
        $prefix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 3));
        $random = strtoupper(Str::random(5));
        return $prefix . '-' . $random;
    }

    private function createThumbnail($imagePath, $width, $height)
    {
        // Get image info
        $imageInfo = getimagesize($imagePath);
        $mime = $imageInfo['mime'];
        
        // Create source image based on file type
        switch ($mime) {
            case 'image/jpeg':
                $source = imagecreatefromjpeg($imagePath);
                break;
            case 'image/png':
                $source = imagecreatefrompng($imagePath);
                break;
            case 'image/gif':
                $source = imagecreatefromgif($imagePath);
                break;
            default:
                return file_get_contents($imagePath); // Fallback for unsupported types
        }
        
        // Get dimensions
        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        
        // Calculate new dimensions while maintaining aspect ratio
        $ratio = min($width / $sourceWidth, $height / $sourceHeight);
        $targetWidth = round($sourceWidth * $ratio);
        $targetHeight = round($sourceHeight * $ratio);
        
        // Create target image
        $target = imagecreatetruecolor($targetWidth, $targetHeight);
        
        // Preserve transparency for PNG images
        if ($mime == 'image/png') {
            imagealphablending($target, false);
            imagesavealpha($target, true);
            $transparent = imagecolorallocatealpha($target, 255, 255, 255, 127);
            imagefilledrectangle($target, 0, 0, $targetWidth, $targetHeight, $transparent);
        }
        
        // Resize image
        imagecopyresampled(
            $target, $source,
            0, 0, 0, 0,
            $targetWidth, $targetHeight, $sourceWidth, $sourceHeight
        );
        
        // Output to buffer
        ob_start();
        switch ($mime) {
            case 'image/jpeg':
                imagejpeg($target, null, 90);
                break;
            case 'image/png':
                imagepng($target, null, 9);
                break;
            case 'image/gif':
                imagegif($target);
                break;
        }
        $imageData = ob_get_clean();
        
        // Free memory
        imagedestroy($source);
        imagedestroy($target);
        
        return $imageData;
    }

    /**
     * Get featured products
     */
    public function featured(Request $request)
    {
        try {
            $limit = $request->input('limit', 8);
            
            $products = Product::with('category')
                ->where('is_active', true)
                ->where('is_featured', true)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();
                
            return response()->json([
                'data' => $products,
                'success' => true
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error loading featured products: ' . $e->getMessage(),
                'success' => false
            ], 500);
        }
    }

    // Store a new comment/question for a product
    public function storeComment(Request $request, Product $product)
    {
        $request->validate([
            'question' => 'required|string|min:1',
        ]);
        
        try {
            $comment = $product->comments()->create([
                'user_id' => $request->user()->id,
                'question' => $request->question,
            ]);
            
            return response()->json([
                'message' => 'Question submitted successfully',
                'data' => $comment->load('replies'),
                'success' => true
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error submitting question: ' . $e->getMessage(),
                'success' => false
            ], 500);
        }
    }
    
    // Store a reply to a product comment (available to all users)
    public function replyToComment(Request $request, Product $product, $commentId)
    {
        $request->validate([
            'reply' => 'required|string|min:1',
        ]);
        
        try {
            \Log::info('Replying to comment', [
                'product_id' => $product->id,
                'comment_id' => $commentId,
                'user_id' => $request->user()->id,
                'is_admin' => $request->user()->isAdmin(),
                'reply' => $request->reply
            ]);
            
            $comment = $product->comments()->findOrFail($commentId);
            
            $reply = $comment->replies()->create([
                'user_id' => $request->user()->id,
                'reply' => $request->reply,
                'is_admin' => $request->user()->isAdmin(),
            ]);
            
            \Log::info('Reply created successfully', [
                'reply_id' => $reply->id
            ]);
            
            return response()->json([
                'message' => 'Reply submitted successfully',
                'data' => $reply,
                'success' => true
            ]);
        } catch (\Exception $e) {
            \Log::error('Error submitting reply: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Error submitting reply: ' . $e->getMessage(),
                'success' => false
            ], 500);
        }
    }
    
    // Admin: Delete a comment (completely)
    public function deleteComment(Request $request, Product $product, $commentId)
    {
        // Check if the user is an admin
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized. Only administrators can delete comments.',
                'success' => false
            ], 403);
        }

        try {
            $comment = $product->comments()->findOrFail($commentId);
            $comment->delete();
            
            return response()->json([
                'message' => 'Comment deleted successfully',
                'success' => true
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting comment: ' . $e->getMessage(),
                'success' => false
            ], 500);
        }
    }

    // Delete a specific reply
    public function deleteReply(Request $request, Product $product, $commentId, $replyId)
    {
        try {
            \Log::info('Deleting reply', [
                'product_id' => $product->id,
                'comment_id' => $commentId,
                'reply_id' => $replyId,
                'user_id' => $request->user()->id,
                'is_admin' => $request->user()->isAdmin()
            ]);
            
            // Check if the user is an admin
            if (!$request->user()->isAdmin()) {
                \Log::warning('Unauthorized delete reply attempt', [
                    'user_id' => $request->user()->id,
                    'is_admin' => $request->user()->isAdmin()
                ]);
                
                return response()->json([
                    'message' => 'Unauthorized. Only administrators can delete replies.',
                    'success' => false
                ], 403);
            }
            
            $comment = $product->comments()->findOrFail($commentId);
            $reply = $comment->replies()->findOrFail($replyId);
            $reply->delete();
            
            \Log::info('Reply deleted successfully');
            
            return response()->json([
                'message' => 'Reply deleted successfully',
                'success' => true
            ]);
        } catch (\Exception $e) {
            \Log::error('Error deleting reply: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Error deleting reply: ' . $e->getMessage(),
                'success' => false
            ], 500);
        }
    }

    public function suggestions(Request $request, $query = null)
    {
        try {
            // Decode URL-encoded query
            $query = urldecode($query);
            \Log::info('ProductController: Getting suggestions for query: ' . $query);
            
            if (empty($query) || strlen($query) < 2) {
                return response()->json([
                    'data' => [],
                    'success' => true
                ]);
            }
            
            // Clean up the query
            $searchTerm = trim($query);
            
            // Log the cleaned search term
            \Log::info('ProductController: Cleaned search term for suggestions: ' . $searchTerm);
            
            // First get products that start with the query (higher priority)
            $startsWithQuery = Product::select('id', 'name', 'slug', 'price', 'images')
                ->where('name', 'ilike', $searchTerm . '%')
                ->limit(5)
                ->get();
                
            // If we have less than 5 results, get additional products that contain the query
            if ($startsWithQuery->count() < 5) {
                $containsQuery = Product::select('id', 'name', 'slug', 'price', 'images')
                    ->where('name', 'ilike', '%' . $searchTerm . '%')
                    ->whereNotIn('id', $startsWithQuery->pluck('id')->toArray())
                    ->limit(5 - $startsWithQuery->count())
                    ->get();
                    
                // Merge the results
                $products = $startsWithQuery->concat($containsQuery);
            } else {
                $products = $startsWithQuery;
            }
                
            \Log::info('ProductController: Found ' . $products->count() . ' suggestions for query: ' . $searchTerm);
            
            // Process images for each product
            $products->each(function($product) {
                if (is_string($product->images)) {
                    $images = json_decode($product->images, true);
                    if ($images && isset($images['main'])) {
                        $product->image_url = '/storage/products/thumbnails/' . $images['main'];
                    }
                } else if (is_array($product->images) && isset($product->images['main'])) {
                    $product->image_url = '/storage/products/thumbnails/' . $product->images['main'];
                }
            });
            
            return response()->json([
                'data' => $products,
                'success' => true
            ]);
        } catch (\Exception $e) {
            \Log::error('ProductController: Error getting suggestions: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error getting suggestions: ' . $e->getMessage(),
                'success' => false
            ], 500);
        }
    }
} 