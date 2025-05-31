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
        $query = Product::with('category')
            ->when($request->filled('category'), function ($query) use ($request) {
                $query->whereHas('category', function ($q) use ($request) {
                    $q->where('slug', $request->category);
                });
            })
            ->when($request->filled('price_min'), function ($query) use ($request) {
                $query->where('price', '>=', $request->price_min);
            })
            ->when($request->filled('price_max'), function ($query) use ($request) {
                $query->where('price', '<=', $request->price_max);
            })
            ->when($request->filled('sort'), function ($query) use ($request) {
                switch ($request->sort) {
                    case 'price_asc':
                        $query->orderBy('price', 'asc');
                        break;
                    case 'price_desc':
                        $query->orderBy('price', 'desc');
                        break;
                    case 'newest':
                        $query->orderBy('created_at', 'desc');
                        break;
                    default:
                        $query->orderBy('name', 'asc');
                }
            });

        $products = $query->paginate($request->input('per_page', 12));

        return response()->json($products);
    }

    public function show(Product $product)
    {
        try {
            $product->load('category');
            
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

    public function search(Request $request)
    {
        $request->validate([
            'q' => ['required', 'string', 'min:2'],
        ]);

        $products = Product::with('category')
            ->where('name', 'ilike', '%' . $request->q . '%')
            ->orWhere('description', 'ilike', '%' . $request->q . '%')
            ->orWhereHas('category', function ($query) use ($request) {
                $query->where('name', 'ilike', '%' . $request->q . '%');
            })
            ->paginate($request->input('per_page', 12));

        return response()->json($products);
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

        $product = Product::create([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'sku' => $request->sku ?? $this->generateSku($request->name),
            'specifications' => $specifications,
            'images' => $images,
            'weight' => $request->weight,
            'is_active' => $request->has('is_active') ? $request->is_active : true,
            'is_featured' => $request->has('is_featured') ? $request->is_featured : false,
        ]);

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product,
        ], 201);
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

        // Update product data
        $product->update([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'price' => $request->price,
            'specifications' => $specifications,
            'weight' => $request->weight,
            'is_active' => $request->has('is_active') ? $request->is_active : $product->is_active,
            'is_featured' => $request->has('is_featured') ? $request->is_featured : $product->is_featured,
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
        ]);
    }

    public function uploadImages(Request $request, Product $product)
    {
        $request->validate([
            'images.*' => ['required', 'image', 'max:5120'], // 5MB max
        ]);

        $images = $product->images ?? [];

        foreach ($request->file('images') as $image) {
            $filename = Str::uuid() . '.' . $image->getClientOriginalExtension();
            
            // Create thumbnail using native PHP GD
            $thumbnail = $this->createThumbnail($image->getRealPath(), 300, 300);
            
            // Store original and thumbnail
            Storage::disk('public')->put('products/original/' . $filename, file_get_contents($image));
            Storage::disk('public')->put('products/thumbnails/' . $filename, $thumbnail);

            $images[] = $filename;
        }

        $product->update(['images' => $images]);

        return response()->json([
            'message' => 'Product images uploaded successfully',
            'product' => $product,
        ]);
    }

    public function destroy(Product $product)
    {
        // Delete product images
        if (!empty($product->images)) {
            foreach ($product->images as $image) {
                Storage::disk('public')->delete('products/original/' . $image);
                Storage::disk('public')->delete('products/thumbnails/' . $image);
            }
        }

        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully',
        ]);
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
} 