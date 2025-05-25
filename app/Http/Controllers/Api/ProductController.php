<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

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
        $product->load('category');
        return response()->json($product);
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
            'specifications' => ['required', 'array'],
            'weight' => ['required', 'numeric', 'min:0'],
            'images.*' => ['required', 'image', 'max:5120'], // 5MB max
        ]);

        $images = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $filename = Str::uuid() . '.' . $image->getClientOriginalExtension();
                
                // Create thumbnail
                $thumbnail = Image::make($image)
                    ->fit(300, 300, function ($constraint) {
                        $constraint->aspectRatio();
                    })
                    ->encode();

                // Store original and thumbnail
                Storage::disk('public')->put('products/original/' . $filename, file_get_contents($image));
                Storage::disk('public')->put('products/thumbnails/' . $filename, $thumbnail);

                $images[] = $filename;
            }
        }

        $product = Product::create([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'sku' => $this->generateSku($request->name),
            'specifications' => $request->specifications,
            'images' => $images,
            'weight' => $request->weight,
            'is_active' => true,
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
            'specifications' => ['required', 'array'],
            'weight' => ['required', 'numeric', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);

        $product->update([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'price' => $request->price,
            'specifications' => $request->specifications,
            'weight' => $request->weight,
            'is_active' => $request->is_active,
        ]);

        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product,
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
            
            // Create thumbnail
            $thumbnail = Image::make($image)
                ->fit(300, 300, function ($constraint) {
                    $constraint->aspectRatio();
                })
                ->encode();

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
} 