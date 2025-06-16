<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function __construct()
    {
        // Uncomment this when Midtrans is properly set up
        /*
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
        */
    }

    public function index(Request $request)
    {
        // Debug information
        \Log::info('OrderController@index - User ID: ' . $request->user()->id);
        \Log::info('OrderController@index - User Email: ' . $request->user()->email);
        
        // Direct database query to check for orders
        $directOrderCount = \DB::table('orders')
            ->where('user_id', $request->user()->id)
            ->count();
        \Log::info('OrderController@index - Direct DB query order count: ' . $directOrderCount);
        
        // Get all orders for debugging
        if ($directOrderCount == 0) {
            $allOrders = \DB::table('orders')->count();
            \Log::info('OrderController@index - Total orders in database: ' . $allOrders);
            
            // Check if there are any orders with this user ID
            $userOrders = \DB::table('orders')
                ->where('user_id', $request->user()->id)
                ->get();
            \Log::info('OrderController@index - User orders from direct query: ' . json_encode($userOrders));
        }
        
        // Get user orders
        $orders = $request->user()
            ->orders()
            ->with(['items.product', 'trackingHistory'])
            ->orderBy('created_at', 'desc');
            
        // Debug order count before pagination
        $orderCount = $orders->count();
        \Log::info('OrderController@index - Total orders before pagination: ' . $orderCount);
        
        // If no orders found, return a more descriptive response
        if ($orderCount == 0) {
            return response()->json([
                'data' => [],
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 10,
                'total' => 0,
                'message' => 'No orders found for this user',
                'user_id' => $request->user()->id,
                'debug_info' => [
                    'direct_query_count' => $directOrderCount,
                    'eloquent_count' => $orderCount,
                    'user_email' => $request->user()->email
                ]
            ]);
        }
        
        // Paginate results
        $paginatedOrders = $orders->paginate(10);
        
        // Debug paginated results
        \Log::info('OrderController@index - Paginated orders count: ' . $paginatedOrders->count());
        \Log::info('OrderController@index - Total orders in pagination: ' . $paginatedOrders->total());

        return response()->json($paginatedOrders);
    }

    public function show(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $order->load(['items.product', 'trackingHistory']);
        return response()->json($order);
    }

    public function store(Request $request)
    {
        $request->validate([
            'shipping_name' => ['required', 'string'],
            'shipping_phone' => ['required', 'string'],
            'shipping_address' => ['required', 'string'],
            'shipping_city' => ['required', 'string'],
            'shipping_province' => ['required', 'string'],
            'shipping_postal_code' => ['required', 'string'],
            'shipping_method' => ['required', 'string'],
            'shipping_cost' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $cart = $request->user()->cart;

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json([
                'message' => 'Cart is empty',
            ], 422);
        }

        // Check stock availability
        foreach ($cart->items as $item) {
            if ($item->product->stock < $item->quantity) {
                return response()->json([
                    'message' => "Insufficient stock for product: {$item->product->name}",
                ], 422);
            }
        }

        // Create order
        $order = Order::create([
            'order_number' => $this->generateOrderNumber(),
            'user_id' => $request->user()->id,
            'subtotal' => $cart->total_amount,
            'shipping_cost' => $request->shipping_cost,
            'total_amount' => $cart->total_amount + $request->shipping_cost,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'shipping_method' => $request->shipping_method,
            'shipping_status' => 'pending',
            'shipping_name' => $request->shipping_name,
            'shipping_phone' => $request->shipping_phone,
            'shipping_address' => $request->shipping_address,
            'shipping_city' => $request->shipping_city,
            'shipping_province' => $request->shipping_province,
            'shipping_postal_code' => $request->shipping_postal_code,
            'notes' => $request->notes,
        ]);

        // Create order items and update stock
        foreach ($cart->items as $item) {
            $order->items()->create([
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'subtotal' => $item->subtotal,
            ]);

            // Update stock
            $product = $item->product;
            $stockBefore = $product->stock;
            $product->decrement('stock', $item->quantity);

            // Create stock history
            StockHistory::create([
                'product_id' => $product->id,
                'quantity_change' => -$item->quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $product->stock,
                'type' => 'out',
                'reference_type' => 'order',
                'reference_id' => $order->id,
                'notes' => "Order #{$order->order_number}",
                'created_by' => $request->user()->id,
            ]);
        }

        // Create initial tracking history
        $order->trackingHistory()->create([
            'status' => 'pending',
            'description' => 'Order placed successfully',
        ]);

        // Clear cart
        $cart->items()->delete();
        $cart->update(['total_amount' => 0]);

        // Generate Midtrans payment token
        $payload = [
            'transaction_details' => [
                'order_id' => $order->order_number,
                'gross_amount' => (int) $order->total_amount,
            ],
            'customer_details' => [
                'first_name' => $request->shipping_name,
                'email' => $request->user()->email,
                'phone' => $request->shipping_phone,
                'shipping_address' => [
                    'first_name' => $request->shipping_name,
                    'phone' => $request->shipping_phone,
                    'address' => $request->shipping_address,
                    'city' => $request->shipping_city,
                    'postal_code' => $request->shipping_postal_code,
                    'country_code' => 'IDN',
                ],
            ],
            'item_details' => $order->items->map(function ($item) {
                return [
                    'id' => $item->product_id,
                    'price' => (int) $item->price,
                    'quantity' => $item->quantity,
                    'name' => $item->product_name,
                ];
            })->push([
                'id' => 'SHIPPING',
                'price' => (int) $order->shipping_cost,
                'quantity' => 1,
                'name' => 'Shipping Cost',
            ])->toArray(),
        ];

        try {
            $snapToken = Snap::getSnapToken($payload);
            $order->update(['midtrans_transaction_id' => $snapToken]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to generate payment token',
                'error' => $e->getMessage(),
            ], 500);
        }

        $order->load(['items.product', 'trackingHistory']);

        return response()->json([
            'message' => 'Order created successfully',
            'order' => $order,
        ], 201);
    }

    public function pay(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        if ($order->payment_status !== 'unpaid') {
            return response()->json([
                'message' => 'Order has already been paid',
            ], 422);
        }

        $request->validate([
            'payment_response' => ['required', 'array'],
        ]);

        $order->update([
            'payment_status' => 'paid',
            'status' => 'processing',
            'midtrans_response' => $request->payment_response,
            'paid_at' => now(),
        ]);

        // Create tracking history
        $order->trackingHistory()->create([
            'status' => 'processing',
            'description' => 'Payment received, order is being processed',
        ]);

        return response()->json([
            'message' => 'Payment processed successfully',
            'order' => $order,
        ]);
    }

    public function trackingStatus(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $tracking = $order->trackingHistory()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($tracking);
    }

    public function adminIndex(Request $request)
    {
        // Debug information
        \Log::info('AdminIndex called', [
            'user' => $request->user() ? [
                'id' => $request->user()->id,
                'email' => $request->user()->email,
                'role' => $request->user()->role,
                'isAdmin' => $request->user()->isAdmin()
            ] : 'No authenticated user',
            'headers' => $request->header(),
            'cookies' => $request->cookies->all(),
            'ip' => $request->ip(),
            'ajax' => $request->ajax(),
            'expectsJson' => $request->expectsJson(),
            'wantsJson' => $request->wantsJson(),
        ]);
        
        // Check if user is authenticated and an admin
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthenticated. Please log in.'], 401);
        }
        
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
        }
        
        $query = Order::with(['user', 'items.product', 'trackingHistory']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('shipping_status')) {
            $query->where('shipping_status', $request->shipping_status);
        }

        $orders = $query->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 10));
            
        \Log::info('Orders fetched', [
            'count' => $orders->count(),
            'total' => $orders->total()
        ]);

        return response()->json($orders);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|string|in:pending,processing,shipped,delivered,cancelled',
        ]);

        $order->status = $request->status;
        $order->save();

        // Add to tracking history
        $order->trackingHistory()->create([
            'status' => $request->status,
            'description' => "Order status updated to {$request->status}",
        ]);

        return response()->json([
            'message' => 'Order status updated successfully',
            'order' => $order->load(['items.product', 'trackingHistory']),
        ]);
    }

    /**
     * Update tracking number for an order
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateTracking(Request $request, Order $order)
    {
        $request->validate([
            'tracking_number' => 'required|string',
        ]);

        $order->tracking_number = $request->tracking_number;
        $order->save();

        // Add to tracking history
        $order->trackingHistory()->create([
            'status' => $order->status,
            'description' => "Tracking number updated to {$request->tracking_number}",
        ]);

        return response()->json([
            'message' => 'Tracking number updated successfully',
            'order' => $order->load(['items.product', 'trackingHistory']),
        ]);
    }

    public function updateShipping(Request $request, Order $order)
    {
        $request->validate([
            'tracking_number' => ['required', 'string'],
            'shipping_status' => ['required', 'string'],
            'description' => ['required', 'string'],
            'location' => ['required', 'string'],
        ]);

        $order->update([
            'tracking_number' => $request->tracking_number,
            'shipping_status' => $request->shipping_status,
        ]);

        // Create tracking history
        $order->trackingHistory()->create([
            'status' => $request->shipping_status,
            'description' => $request->description,
            'location' => $request->location,
        ]);

        return response()->json([
            'message' => 'Shipping information updated successfully',
            'order' => $order,
        ]);
    }

    /**
     * Update seat receipt number for an order
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSeatReceipt(Request $request, Order $order)
    {
        $request->validate([
            'seat_receipt_number' => 'required|string',
        ]);

        $order->seat_receipt_number = $request->seat_receipt_number;
        
        // If we're setting a seat receipt, update the status to processing if it's still pending
        if ($order->status === 'pending') {
            $order->status = 'processing';
        }
        
        $order->save();

        // Add to tracking history
        $order->trackingHistory()->create([
            'status' => $order->status,
            'description' => "Seat receipt number updated to {$request->seat_receipt_number}",
        ]);

        return response()->json([
            'message' => 'Seat receipt number updated successfully',
            'order' => $order->load(['items.product', 'trackingHistory']),
        ]);
    }

    private function generateOrderNumber()
    {
        $prefix = 'ORD';
        $date = now()->format('Ymd');
        $random = strtoupper(Str::random(4));
        return "{$prefix}-{$date}-{$random}";
    }
} 