<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class CheckoutController extends Controller
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
     * Show the payment page
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function payment(Request $request)
    {
        $user = $request->user();
        $cart = $user->cart;

        if (!$cart || $cart->items->count() === 0) {
            return redirect()->route('cart')->with('error', 'Your cart is empty');
        }

        // Get shipping details from session if available
        $shippingDetails = Session::get('shipping_details', []);

        // Calculate total weight (250g per item)
        $cartWeight = $cart->items->sum(function ($item) {
            return $item->quantity * 250; // 250g per item
        });

        return view('checkout.payment', compact('cart', 'shippingDetails', 'cartWeight'));
    }

    /**
     * Process the checkout
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function process(Request $request)
    {
        $request->validate([
            'shipping_name' => 'required|string|max:255',
            'shipping_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string',
            'shipping_city' => 'required|string',
            'shipping_province' => 'required|string',
            'shipping_postal_code' => 'required|string|max:10',
            'shipping_cost' => 'required|numeric|min:0',
            'shipping_service' => 'required|string',
            'payment_method' => 'required|string|in:midtrans,bank_transfer,cash_on_delivery',
        ]);

        $user = $request->user();
        $cart = $user->cart;

        if (!$cart || $cart->items->count() === 0) {
            return redirect()->route('cart')->with('error', 'Your cart is empty');
        }

        try {
            DB::beginTransaction();

            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => 'ORD-' . time() . '-' . $user->id,
                'status' => 'pending',
                'shipping_name' => $request->shipping_name,
                'shipping_phone' => $request->shipping_phone,
                'shipping_address' => $request->shipping_address,
                'shipping_city' => $request->shipping_city,
                'shipping_province' => $request->shipping_province,
                'shipping_postal_code' => $request->shipping_postal_code,
                'shipping_cost' => $request->shipping_cost,
                'shipping_service' => $request->shipping_service,
                'payment_method' => $request->payment_method,
                'subtotal' => $cart->total_amount,
                'total_amount' => $cart->total_amount + $request->shipping_cost,
                'notes' => $request->notes,
            ]);

            // Create order items
            foreach ($cart->items as $cartItem) {
                $product = $cartItem->product;

                // Check stock availability
                if ($product->stock < $cartItem->quantity) {
                    throw new \Exception("Insufficient stock for {$product->name}. Only {$product->stock} available.");
                }

                // Create order item
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->price,
                    'subtotal' => $cartItem->subtotal,
                ]);

                // Update product stock
                $product->update([
                    'stock' => $product->stock - $cartItem->quantity
                ]);
            }

            // Clear cart
            $cart->items()->delete();
            $cart->update(['total_amount' => 0]);

            DB::commit();

            // Store order ID in session for confirmation page
            Session::put('order_id', $order->id);

            // If payment method is Midtrans, redirect to Midtrans payment page
            if ($request->payment_method === 'midtrans') {
                // Implement Midtrans payment gateway integration here
                // For now, we'll just redirect to the confirmation page
                return redirect()->route('checkout.confirmation');
            }

            return redirect()->route('checkout.confirmation');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Show the confirmation page
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function confirmation(Request $request)
    {
        $orderId = Session::get('order_id');

        if (!$orderId) {
            return redirect()->route('orders.index')->with('error', 'Order not found');
        }

        $order = Order::with('items.product')->findOrFail($orderId);

        // Clear session
        Session::forget('order_id');
        Session::forget('shipping_details');

        return view('checkout.confirmation', compact('order'));
    }
} 