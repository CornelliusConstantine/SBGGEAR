<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the user's shopping cart
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to view your cart');
        }
        
        $cart = $this->getUserCart($request->user());
        return view('cart', compact('cart'));
    }

    /**
     * Add a product to the cart
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);
        
        $product = Product::findOrFail($request->product_id);
        $quantity = $request->quantity;
        
        // Check if product is in stock
        if ($product->stock <= 0) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product is out of stock'
                ]);
            }
            return back()->with('error', 'Product is out of stock');
        }
        
        // Check if requested quantity is available
        if ($product->stock < $quantity) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "Only {$product->stock} units available"
                ]);
            }
            return back()->with('error', "Only {$product->stock} units available");
        }
        
        $cart = $this->getUserCart($request->user());
        
        // Check if product is already in cart
        $cartItem = $cart->items()->where('product_id', $product->id)->first();
        
        if ($cartItem) {
            // Update quantity if product already exists in cart
            $newQuantity = $cartItem->quantity + $quantity;
            
            // Check if new total quantity exceeds available stock
            if ($newQuantity > $product->stock) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => "Cannot add more. You already have {$cartItem->quantity} in your cart and only {$product->stock} units are available"
                    ]);
                }
                return back()->with('error', "Cannot add more. You already have {$cartItem->quantity} in your cart and only {$product->stock} units are available");
            }
            
            $cartItem->update([
                'quantity' => $newQuantity,
                'subtotal' => $newQuantity * $product->price
            ]);
        } else {
            // Add new cart item
            $cart->items()->create([
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price' => $product->price,
                'subtotal' => $quantity * $product->price
            ]);
        }
        
        // Update cart total
        $cart->update([
            'total_amount' => $cart->items()->sum('subtotal')
        ]);
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Product added to cart successfully',
                'cart_count' => $cart->items()->sum('quantity')
            ]);
        }
        
        return back()->with('success', 'Product added to cart successfully');
    }

    /**
     * Update cart item quantity
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $request->validate([
            'cart_item_id' => 'required|exists:cart_items,id',
            'quantity' => 'required|integer|min:1',
        ]);
        
        $cart = $this->getUserCart($request->user());
        $cartItem = $cart->items()->findOrFail($request->cart_item_id);
        $product = $cartItem->product;
        
        // Check if requested quantity is available
        if ($product->stock < $request->quantity) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "Only {$product->stock} units available"
                ]);
            }
            return back()->with('error', "Only {$product->stock} units available");
        }
        
        // Update cart item
        $cartItem->update([
            'quantity' => $request->quantity,
            'subtotal' => $request->quantity * $cartItem->price
        ]);
        
        // Update cart total
        $cart->update([
            'total_amount' => $cart->items()->sum('subtotal')
        ]);
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cart updated successfully',
                'item_subtotal' => $cartItem->subtotal,
                'cart_total' => $cart->total_amount,
                'cart_count' => $cart->items()->sum('quantity')
            ]);
        }
        
        return back()->with('success', 'Cart updated successfully');
    }

    /**
     * Remove item from cart
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function remove(Request $request)
    {
        $request->validate([
            'cart_item_id' => 'required|exists:cart_items,id',
        ]);
        
        $cart = $this->getUserCart($request->user());
        $cartItem = $cart->items()->findOrFail($request->cart_item_id);
        
        // Delete cart item
        $cartItem->delete();
        
        // Update cart total
        $cart->update([
            'total_amount' => $cart->items()->sum('subtotal')
        ]);
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart',
                'cart_total' => $cart->total_amount,
                'cart_count' => $cart->items()->sum('quantity')
            ]);
        }
        
        return back()->with('success', 'Item removed from cart');
    }

    /**
     * Get or create a cart for the user
     */
    private function getUserCart($user)
    {
        $cart = $user->cart;

        if (!$cart) {
            $cart = Cart::create([
                'user_id' => $user->id,
                'total_amount' => 0,
            ]);
        }
        
        $cart->load('items.product');

        return $cart;
    }
} 