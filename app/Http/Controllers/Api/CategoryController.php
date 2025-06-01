<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    public function index()
    {
        Log::info('Fetching all categories');
        $categories = Category::orderBy('name')->get();
        Log::info('Categories found: ' . $categories->count());
        
        return response()->json([
            'data' => $categories
        ]);
    }

    public function show($slug)
    {
        try {
            Log::info('Fetching category with slug: ' . $slug);
            $category = Category::where('slug', $slug)->first();
            
            if (!$category) {
                Log::warning('Category not found with slug: ' . $slug);
                return response()->json([
                    'message' => 'Category not found'
                ], 404);
            }
            
            Log::info('Category found: ' . $category->name);
            return response()->json([
                'data' => $category
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching category: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error fetching category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'description' => ['required', 'string'],
            ]);

            // Generate a unique slug
            $baseName = $request->name;
            $slug = Str::slug($baseName);
            
            // If the slug already exists, add a random string to make it unique
            if (Category::where('slug', $slug)->exists()) {
                $slug = $slug . '-' . Str::random(5);
            }

            $category = Category::create([
                'name' => $baseName,
                'slug' => $slug,
                'description' => $request->description,
                'is_active' => true,
            ]);

            return response()->json([
                'message' => 'Category created successfully',
                'category' => $category,
            ], 201);
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Category creation failed: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to create category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Category $category)
    {
        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'description' => ['required', 'string'],
                'is_active' => ['required', 'boolean'],
            ]);

            // Generate a slug from the name
            $baseName = $request->name;
            $slug = Str::slug($baseName);
            
            // If the name has changed and the new slug would conflict with an existing one
            if ($slug !== $category->slug && Category::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
                // Add a random string to make it unique
                $slug = $slug . '-' . Str::random(5);
            }

            $category->update([
                'name' => $baseName,
                'slug' => $slug,
                'description' => $request->description,
                'is_active' => $request->is_active,
            ]);

            return response()->json([
                'message' => 'Category updated successfully',
                'category' => $category,
            ]);
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Category update failed: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to update category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Category $category)
    {
        // Check if category has products
        if ($category->products()->exists()) {
            return response()->json([
                'message' => 'Cannot delete category with existing products',
            ], 422);
        }

        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully',
        ]);
    }
} 