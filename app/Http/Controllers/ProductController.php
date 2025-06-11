<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Product::where('is_active', true)
                        ->where('stock', '>', 0); // Only show products with stock > 0
        
        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhere('sku', 'like', '%' . $search . '%');
            });
        }
        
        // Category filter
        if ($request->has('category') && !empty($request->category)) {
            $query->where('category_id', $request->category);
        }
        
        // Price filter
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }
        
        // Stock filter - include out of stock products if requested
        if ($request->has('include_out_of_stock') && $request->include_out_of_stock) {
            $query->where('is_active', true); // Reset the stock condition
        }
        
        // Sort options
        $sortBy = $request->sort_by ?? 'created_at';
        $sortDir = $request->sort_dir ?? 'desc';
        
        $allowedSorts = ['name', 'price', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }
        
        if (!in_array($sortDir, ['asc', 'desc'])) {
            $sortDir = 'desc';
        }
        
        $query->orderBy($sortBy, $sortDir);
        
        $products = $query->paginate(12);
        $categories = Category::all();
        
        return view('products.index', compact('products', 'categories'));
    }
    
    /**
     * Display products by category.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\View\View
     */
    public function byCategory(Category $category, Request $request)
    {
        $query = Product::where('category_id', $category->id)
            ->where('is_active', true)
            ->where('stock', '>', 0); // Only show products with stock > 0
            
        // Include out of stock products if requested
        if ($request->has('include_out_of_stock') && $request->include_out_of_stock) {
            $query = Product::where('category_id', $category->id)
                ->where('is_active', true);
        }
        
        $products = $query->orderBy('created_at', 'desc')
            ->paginate(12);
            
        $categories = Category::all();
        
        return view('products.index', compact('products', 'categories', 'category'));
    }

    /**
     * Display the specified product.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\View\View
     */
    public function show(Product $product)
    {
        if (!$product->is_active) {
            abort(404);
        }
        
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->where('stock', '>', 0) // Only show related products with stock > 0
            ->inRandomOrder()
            ->limit(4)
            ->get();
            
        return view('products.show', compact('product', 'relatedProducts'));
    }
    
    /**
     * Display new arrivals.
     *
     * @return \Illuminate\View\View
     */
    public function newArrivals(Request $request)
    {
        $query = Product::where('is_active', true)
            ->where('stock', '>', 0) // Only show products with stock > 0
            ->orderBy('created_at', 'desc');
            
        // Include out of stock products if requested
        if ($request->has('include_out_of_stock') && $request->include_out_of_stock) {
            $query = Product::where('is_active', true)
                ->orderBy('created_at', 'desc');
        }
        
        $products = $query->paginate(12);
        $categories = Category::all();
        
        return view('products.index', [
            'products' => $products,
            'categories' => $categories,
            'title' => 'New Arrivals'
        ]);
    }
    
    /**
     * Display products on sale.
     *
     * @return \Illuminate\View\View
     */
    public function onSale(Request $request)
    {
        $query = Product::where('is_active', true)
            ->where('is_featured', true)
            ->where('stock', '>', 0) // Only show products with stock > 0
            ->orderBy('created_at', 'desc');
            
        // Include out of stock products if requested
        if ($request->has('include_out_of_stock') && $request->include_out_of_stock) {
            $query = Product::where('is_active', true)
                ->where('is_featured', true)
                ->orderBy('created_at', 'desc');
        }
        
        $products = $query->paginate(12);
        $categories = Category::all();
        
        return view('products.index', [
            'products' => $products,
            'categories' => $categories,
            'title' => 'On Sale'
        ]);
    }
} 