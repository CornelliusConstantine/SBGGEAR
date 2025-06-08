<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
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
        $this->middleware('auth:sanctum');
    }
    
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'message' => 'Unauthorized. Please log in to continue.',
            ], 401);
        }
        
        $cart = $this->getUserCart($request->user());
        $cart->load('items.product');

        // Check stock availability for each item
        foreach ($cart->items as $item) {
            $item->stock_status = $this->checkStockStatus($item);
        }

        return response()->json($cart);
    }

    public function store(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'message' => 'Unauthorized. Please log in to continue.',
            ], 401);
        }
        
        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $product = Product::findOrFail($request->product_id);

        if ($product->stock < $request->quantity) {
            return response()->json([
                'message' => 'Insufficient stock available',
            ], 422);
        }

        $cart = $this->getUserCart($request->user());
        $cartItem = $cart->items()->where('product_id', $product->id)->first();

        if ($cartItem) {
            if ($product->stock < ($cartItem->quantity + $request->quantity)) {
                return response()->json([
                    'message' => 'Insufficient stock available',
                ], 422);
            }

            $cartItem->update([
                'quantity' => $cartItem->quantity + $request->quantity,
                'subtotal' => ($cartItem->quantity + $request->quantity) * $product->price,
            ]);
        } else {
            $cartItem = $cart->items()->create([
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'price' => $product->price,
                'subtotal' => $request->quantity * $product->price,
            ]);
        }

        // Update cart total
        $cart->update([
            'total_amount' => $cart->items()->sum('subtotal'),
        ]);

        $cart->load('items.product');

        // Check stock availability for each item
        foreach ($cart->items as $item) {
            $item->stock_status = $this->checkStockStatus($item);
        }

        return response()->json([
            'message' => 'Item added to cart successfully',
            'cart' => $cart,
        ]);
    }

    public function update(Request $request, CartItem $cartItem)
    {
        if (!Auth::check() || $cartItem->cart->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        if ($cartItem->product->stock < $request->quantity) {
            return response()->json([
                'message' => 'Insufficient stock available',
                'available_stock' => $cartItem->product->stock
            ], 422);
        }

        $cartItem->update([
            'quantity' => $request->quantity,
            'subtotal' => $request->quantity * $cartItem->price,
        ]);

        // Update cart total
        $cartItem->cart->update([
            'total_amount' => $cartItem->cart->items()->sum('subtotal'),
        ]);

        $cartItem->cart->load('items.product');

        // Check stock availability for each item
        foreach ($cartItem->cart->items as $item) {
            $item->stock_status = $this->checkStockStatus($item);
        }

        return response()->json([
            'message' => 'Cart item updated successfully',
            'cart' => $cartItem->cart,
        ]);
    }

    public function destroy(Request $request, CartItem $cartItem)
    {
        if (!Auth::check() || $cartItem->cart->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $cart = $cartItem->cart;
        $cartItem->delete();

        // Update cart total
        $cart->update([
            'total_amount' => $cart->items()->sum('subtotal'),
        ]);

        $cart->load('items.product');

        // Check stock availability for each item
        foreach ($cart->items as $item) {
            $item->stock_status = $this->checkStockStatus($item);
        }

        return response()->json([
            'message' => 'Item removed from cart successfully',
            'cart' => $cart,
        ]);
    }

    /**
     * Clear all items from the user's cart
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function clear(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'message' => 'Unauthorized. Please log in to continue.',
            ], 401);
        }
        
        $cart = $this->getUserCart($request->user());
        
        // Delete all cart items
        $cart->items()->delete();
        
        // Update cart total
        $cart->update([
            'total_amount' => 0,
        ]);
        
        $cart->load('items.product');
        
        return response()->json([
            'message' => 'Cart cleared successfully',
            'cart' => $cart,
        ]);
    }

    /**
     * Check stock availability for a cart item
     * 
     * @param CartItem $cartItem
     * @return array
     */
    private function checkStockStatus($cartItem)
    {
        $product = $cartItem->product;
        $status = [
            'available' => true,
            'message' => '',
            'available_stock' => $product->stock
        ];

        if ($product->stock <= 0) {
            $status['available'] = false;
            $status['message'] = 'This product is out of stock';
        } elseif ($product->stock < $cartItem->quantity) {
            $status['available'] = false;
            $status['message'] = "Only {$product->stock} items available";
        }

        return $status;
    }

    /**
     * Get or create a cart for the user
     */
    private function getUserCart($user)
    {
        if (!$user) {
            return null;
        }
        
        $cart = $user->cart;

        if (!$cart) {
            $cart = Cart::create([
                'user_id' => $user->id,
                'total_amount' => 0,
            ]);
        }

        return $cart;
    }
} 