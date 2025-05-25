<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function show(Request $request)
    {
        $cart = $request->user()->cart;

        if (!$cart) {
            $cart = Cart::create([
                'user_id' => $request->user()->id,
                'total_amount' => 0,
            ]);
        }

        $cart->load('items.product');

        return response()->json($cart);
    }

    public function addItem(Request $request)
    {
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

        $cart = $request->user()->cart;

        if (!$cart) {
            $cart = Cart::create([
                'user_id' => $request->user()->id,
                'total_amount' => 0,
            ]);
        }

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

        return response()->json([
            'message' => 'Item added to cart successfully',
            'cart' => $cart,
        ]);
    }

    public function updateItem(Request $request, CartItem $cartItem)
    {
        if ($cartItem->cart->user_id !== $request->user()->id) {
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

        return response()->json([
            'message' => 'Cart item updated successfully',
            'cart' => $cartItem->cart,
        ]);
    }

    public function removeItem(Request $request, CartItem $cartItem)
    {
        if ($cartItem->cart->user_id !== $request->user()->id) {
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

        return response()->json([
            'message' => 'Item removed from cart successfully',
            'cart' => $cart,
        ]);
    }
} 