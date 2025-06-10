<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockHistory;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    protected $midtransService;
    
    public function __construct(MidtransService $midtransService)
    {
        $this->middleware('auth');
        $this->midtransService = $midtransService;
    }

    /**
     * Main checkout entry point
     */
    public function index()
    {
        $user = Auth::user();
        $cart = $this->getUserCart($user);
        
        // Check if cart is empty
        if ($cart->items->isEmpty()) {
            return redirect()->route('products.index')->with('error', 'Your cart is empty. Please add products before checkout.');
        }
        
        // Render the checkout index view with cart data
        return view('checkout.index', compact('cart'));
    }

    /**
     * Show the cart page before proceeding to checkout
     */
    public function cart()
    {
        $user = Auth::user();
        $cart = $this->getUserCart($user);
        
        // Check if cart is empty
        if ($cart->items->isEmpty()) {
            return redirect()->route('products.index')->with('error', 'Your cart is empty. Please add products before checkout.');
        }
        
        // Check stock availability
        $stockIssues = [];
        foreach ($cart->items as $item) {
            if ($item->product->stock < $item->quantity) {
                $stockIssues[] = "Only {$item->product->stock} units available for {$item->product->name}";
            }
        }
        
        if (!empty($stockIssues)) {
            return redirect()->route('cart')->with('error', implode('<br>', $stockIssues));
        }
        
        return view('cart', compact('cart'));
    }

    /**
     * Show the shipping information form
     */
    public function shipping()
    {
        $user = Auth::user();
        $cart = $this->getUserCart($user);
        
        // Check if cart is empty
        if ($cart->items->isEmpty()) {
            return redirect()->route('products.index')->with('error', 'Your cart is empty. Please add products before checkout.');
        }
        
        // Calculate total weight
        $totalWeight = 0;
        foreach ($cart->items as $item) {
            $totalWeight += $item->product->weight * $item->quantity;
        }
        
        // Prepare shipping options
        $shippingOptions = [
            'jne' => [
                'name' => 'JNE',
                'services' => [
                    'regular' => [
                        'name' => 'Regular',
                        'rate' => 9000,
                        'estimate' => '2-3 days'
                    ],
                    'express' => [
                        'name' => 'Express',
                        'rate' => 15000,
                        'estimate' => '1-2 days'
                    ]
                ]
            ],
            'jnt' => [
                'name' => 'J&T',
                'services' => [
                    'regular' => [
                        'name' => 'Regular',
                        'rate' => 8000,
                        'estimate' => '2-4 days'
                    ],
                    'express' => [
                        'name' => 'Express',
                        'rate' => 12000,
                        'estimate' => '1-2 days'
                    ]
                ]
            ],
            'sicepat' => [
                'name' => 'SiCepat',
                'services' => [
                    'regular' => [
                        'name' => 'Regular',
                        'rate' => 8500,
                        'estimate' => '2-3 days'
                    ],
                    'express' => [
                        'name' => 'Express',
                        'rate' => 13000,
                        'estimate' => '1-2 days'
                    ]
                ]
            ]
        ];
        
        // Get list of cities
        $cities = [
            'Jakarta' => 'Jakarta',
            'Bandung' => 'Bandung',
            'Surabaya' => 'Surabaya',
            'Medan' => 'Medan',
            'Bekasi' => 'Bekasi',
            'Tangerang' => 'Tangerang',
            'Depok' => 'Depok',
            'Semarang' => 'Semarang',
            'Palembang' => 'Palembang',
            'Makassar' => 'Makassar',
            'Yogyakarta' => 'Yogyakarta',
            'Bogor' => 'Bogor',
            'Malang' => 'Malang',
            'Padang' => 'Padang',
            'Denpasar' => 'Denpasar'
        ];
        
        return view('checkout.shipping', compact('cart', 'totalWeight', 'shippingOptions', 'cities'));
    }
    
    /**
     * Process shipping information and proceed to payment
     */
    public function processShipping(Request $request)
    {
        $request->validate([
            'shipping_name' => ['required', 'string', 'min:3'],
            'shipping_phone' => ['required', 'string', 'min:10'],
            'shipping_address' => ['required', 'string', 'min:5'],
            'shipping_city' => ['required', 'string'],
            'shipping_postal_code' => ['required', 'string', 'regex:/^[0-9]{5}$/'],
            'courier' => ['required', 'string'],
            'service' => ['required', 'string'],
            'shipping_cost' => ['required', 'numeric', 'min:0']
        ]);
        
        $user = Auth::user();
        $cart = $this->getUserCart($user);
        
        // Store shipping info in session
        session([
            'checkout' => [
                'shipping_info' => [
                    'name' => $request->shipping_name,
                    'phone' => $request->shipping_phone,
                    'address' => $request->shipping_address,
                    'city' => $request->shipping_city,
                    'postal_code' => $request->shipping_postal_code,
                ],
                'shipping_method' => [
                    'courier' => $request->courier,
                    'service' => $request->service,
                    'cost' => $request->shipping_cost
                ],
                'notes' => $request->notes
            ]
        ]);
        
        return redirect()->route('checkout.payment');
    }
    
    /**
     * Show the payment page
     */
    public function payment()
    {
        $user = Auth::user();
        $cart = $this->getUserCart($user);
        
        // Check if shipping info exists in session
        if (!session()->has('checkout.shipping_info')) {
            return redirect()->route('checkout.shipping')->with('error', 'Please complete shipping information first.');
        }
        
        $shippingInfo = session('checkout.shipping_info');
        $shippingMethod = session('checkout.shipping_method');
        $notes = session('checkout.notes');
        
        // Generate order number
        $orderNumber = $this->generateOrderNumber();
        
        // Calculate total amount
        $totalAmount = $cart->total_amount + $shippingMethod['cost'];
        
        // Create temporary order for Midtrans
        $order = new Order([
            'order_number' => $orderNumber,
            'user_id' => $user->id,
            'subtotal' => $cart->total_amount,
            'shipping_cost' => $shippingMethod['cost'],
            'total_amount' => $totalAmount,
            'shipping_method' => $shippingMethod['courier'] . ' ' . $shippingMethod['service'],
            'shipping_name' => $shippingInfo['name'],
            'shipping_phone' => $shippingInfo['phone'],
            'shipping_address' => $shippingInfo['address'],
            'shipping_city' => $shippingInfo['city'],
            'shipping_province' => 'Indonesia', // Default for now
            'shipping_postal_code' => $shippingInfo['postal_code'],
        ]);
        
        // Set relationship with user
        $order->setRelation('user', $user);
        
        // Set relationship with items
        $order->setRelation('items', $cart->items);
        
        try {
            // Get Snap Token from Midtrans service
            $snapToken = $this->midtransService->getSnapToken($order);
            
            // Store order number and snap token in session
            session([
                'checkout.order_number' => $orderNumber,
                'checkout.snap_token' => $snapToken
            ]);
            
            return view('checkout.payment', compact('cart', 'shippingInfo', 'shippingMethod', 'notes', 'totalAmount', 'snapToken', 'orderNumber'));
        } catch (\Exception $e) {
            Log::error('Midtrans error: ' . $e->getMessage());
            return redirect()->route('checkout.shipping')->with('error', 'Payment gateway error. Please try again later.');
        }
    }
    
    /**
     * Process payment and create order
     */
    public function processPayment(Request $request)
    {
        $user = Auth::user();
        $cart = $this->getUserCart($user);
        
        // Check if order number exists in session
        if (!session()->has('checkout.order_number')) {
            return redirect()->route('checkout.shipping')->with('error', 'Invalid checkout session. Please try again.');
        }
        
        $orderNumber = session('checkout.order_number');
        $shippingInfo = session('checkout.shipping_info');
        $shippingMethod = session('checkout.shipping_method');
        $notes = session('checkout.notes');
        
        // Validate Midtrans response
        $request->validate([
            'transaction_status' => 'required',
            'transaction_id' => 'required',
            'payment_type' => 'required',
        ]);
        
        // Determine order status based on transaction status
        $orderStatus = 'pending';
        $paymentStatus = 'pending';
        $paidAt = null;
        
        // Process different transaction statuses
        switch ($request->transaction_status) {
            case 'capture':
            case 'settlement':
                $orderStatus = 'processing';
                $paymentStatus = 'paid';
                $paidAt = now();
                break;
                
            case 'pending':
                $orderStatus = 'pending';
                $paymentStatus = 'pending';
                break;
                
            case 'deny':
            case 'cancel':
            case 'expire':
            case 'failure':
                $orderStatus = 'cancelled';
                $paymentStatus = 'failed';
                break;
                
            default:
                $orderStatus = 'pending';
                $paymentStatus = 'pending';
                break;
        }
        
        // Log the payment response
        Log::info('Midtrans payment response', [
            'order_number' => $orderNumber,
            'transaction_status' => $request->transaction_status,
            'payment_type' => $request->payment_type,
            'transaction_id' => $request->transaction_id
        ]);
        
        // Create order
        $order = Order::create([
            'order_number' => $orderNumber,
            'user_id' => $user->id,
            'subtotal' => $cart->total_amount,
            'shipping_cost' => $shippingMethod['cost'],
            'total_amount' => $cart->total_amount + $shippingMethod['cost'],
            'status' => $orderStatus,
            'payment_status' => $paymentStatus,
            'payment_method' => $request->payment_type,
            'midtrans_transaction_id' => $request->transaction_id,
            'midtrans_response' => $request->all(),
            'shipping_method' => $shippingMethod['courier'] . ' ' . $shippingMethod['service'],
            'shipping_name' => $shippingInfo['name'],
            'shipping_phone' => $shippingInfo['phone'],
            'shipping_address' => $shippingInfo['address'],
            'shipping_city' => $shippingInfo['city'],
            'shipping_province' => 'Indonesia', // Default for now
            'shipping_postal_code' => $shippingInfo['postal_code'],
            'notes' => $notes,
            'paid_at' => $paidAt,
        ]);
        
        // Create order items
        foreach ($cart->items as $item) {
            $order->items()->create([
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'subtotal' => $item->subtotal,
            ]);
            
            // Update stock
            $product = Product::find($item->product_id);
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
                'created_by' => $user->id,
            ]);
        }
        
        // Create tracking history
        $order->trackingHistory()->create([
            'status' => 'pending',
            'description' => 'Order placed successfully',
        ]);
        
        // Clear cart
        $cart->items()->delete();
        $cart->update(['total_amount' => 0]);
        
        // Clear checkout session
        session()->forget('checkout');
        
        return redirect()->route('checkout.confirmation', $order->id);
    }
    
    /**
     * Show order confirmation page
     */
    public function confirmation(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }
        
        $order->load('items.product', 'trackingHistory');
        
        return view('checkout.confirmation', compact('order'));
    }
    
    /**
     * Handle Midtrans webhook notifications
     */
    public function webhook(Request $request)
    {
        try {
            $notification = new \Midtrans\Notification();
            
            // Get notification object from Midtrans
            $notificationBody = json_decode($request->getContent(), true);
            
            // Log the raw notification for debugging
            Log::info('Midtrans webhook received', $notificationBody);
            
            // Validate notification
            if (!isset($notificationBody['transaction_status']) || !isset($notificationBody['order_id'])) {
                Log::error('Invalid Midtrans notification', $notificationBody);
                return response()->json(['status' => 'error', 'message' => 'Invalid notification'], 400);
            }
            
            // Process notification
            $result = $this->midtransService->handleNotification($notificationBody);
            
            if ($result['status'] === 'error') {
                Log::error('Error processing Midtrans notification', ['error' => $result['message'], 'data' => $notificationBody]);
                return response()->json($result, 404);
            }
            
            Log::info('Midtrans notification processed successfully', [
                'order_id' => $notificationBody['order_id'],
                'status' => $notificationBody['transaction_status']
            ]);
            
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Exception in Midtrans webhook handler', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error'
            ], 500);
        }
    }
    
    /**
     * Calculate shipping cost based on weight and courier
     */
    public function calculateShipping(Request $request)
    {
        $request->validate([
            'weight' => 'required|numeric|min:1',
            'courier' => 'required|string',
            'city' => 'required|string'
        ]);
        
        $weight = $request->weight;
        $courier = $request->courier;
        
        // Weight calculation (ceil to nearest 250g)
        $weightMultiplier = ceil($weight / 250);
        
        // Get shipping rates based on courier
        $shippingRates = [
            'jne' => [
                'regular' => 9000,
                'express' => 15000
            ],
            'jnt' => [
                'regular' => 8000,
                'express' => 12000
            ],
            'sicepat' => [
                'regular' => 8500,
                'express' => 13000
            ]
        ];
        
        $services = [];
        
        if (isset($shippingRates[$courier])) {
            foreach ($shippingRates[$courier] as $service => $rate) {
                $cost = $weightMultiplier * $rate;
                
                $estimateMap = [
                    'jne' => [
                        'regular' => '2-3',
                        'express' => '1-2'
                    ],
                    'jnt' => [
                        'regular' => '2-4',
                        'express' => '1-2'
                    ],
                    'sicepat' => [
                        'regular' => '2-3',
                        'express' => '1-2'
                    ]
                ];
                
                $estimate = $estimateMap[$courier][$service] ?? '1-4';
                
                $services[] = [
                    'service' => $service,
                    'description' => ucfirst($service),
                    'cost' => $cost,
                    'estimate' => $estimate . ' days'
                ];
            }
        }
        
        return response()->json([
            'courier' => $courier,
            'services' => $services
        ]);
    }
    
    /**
     * Generate unique order number
     */
    private function generateOrderNumber()
    {
        $prefix = 'ORD-' . date('Ymd') . '-';
        $suffix = strtoupper(Str::random(5));
        $orderNumber = $prefix . $suffix;
        
        // Check if order number already exists
        while (Order::where('order_number', $orderNumber)->exists()) {
            $suffix = strtoupper(Str::random(5));
            $orderNumber = $prefix . $suffix;
        }
        
        return $orderNumber;
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