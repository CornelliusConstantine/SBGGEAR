<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;

class PaymentController extends Controller
{
    protected $midtransService;
    
    public function __construct(MidtransService $midtransService)
    {
        $this->middleware('auth:sanctum')->except(['webhook']);
        $this->midtransService = $midtransService;
    }
    
    /**
     * Get available payment methods
     */
    public function methods()
    {
        return response()->json([
            'methods' => [
                [
                    'id' => 'midtrans',
                    'name' => 'Midtrans Payment Gateway',
                    'description' => 'Pay securely using credit card, bank transfer, or e-wallet',
                    'icon' => 'https://www.midtrans.com/assets/images/logo-midtrans-color.png',
                ]
            ]
        ]);
    }
    
    /**
     * Generate payment token for Midtrans
     */
    public function getToken(Request $request)
    {
        Log::info('Payment token request received', [
            'user_id' => $request->user()->id,
            'headers' => $request->headers->all(),
            'request_data' => $request->all(),
            'midtrans_config' => [
                'server_key' => !empty(Config::$serverKey) ? 'Set' : 'Not set',
                'client_key' => !empty(Config::$clientKey) ? 'Set' : 'Not set',
                'is_production' => Config::$isProduction ? 'true' : 'false',
                'api_endpoint' => Config::$isProduction ? 'https://app.midtrans.com' : 'https://app.sandbox.midtrans.com',
            ]
        ]);
        
        $request->validate([
            'shipping_info' => 'required|array',
            'order_summary' => 'required|array',
        ]);
        
        try {
            // Verify Midtrans configuration
            if (empty(Config::$serverKey)) {
                Log::error('Midtrans server key is not configured');
                return response()->json([
                    'message' => 'Payment gateway configuration error',
                    'error' => 'Server key not configured',
                ], 500);
            }

            // Double-check that Midtrans is configured properly
            if (!class_exists('\\Midtrans\\Config')) {
                Log::error('Midtrans PHP SDK not found');
                return response()->json([
                    'message' => 'Payment gateway not available',
                    'error' => 'Midtrans SDK not found',
                ], 500);
            }

            // Create a temporary order object for Midtrans
            $orderNumber = 'TMP-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 8));
            $shippingInfo = $request->shipping_info;
            $orderSummary = $request->order_summary;
            
            Log::info('Creating order for Midtrans', [
                'order_number' => $orderNumber,
                'shipping_info' => $shippingInfo,
                'order_summary' => $orderSummary
            ]);
            
            $order = new \stdClass();
            $order->order_number = $orderNumber;
            $order->total_amount = $orderSummary['total'];
            $order->shipping_name = $shippingInfo['name'];
            $order->shipping_phone = $shippingInfo['phone'];
            $order->shipping_address = $shippingInfo['address'];
            $order->shipping_city = $shippingInfo['cityName'] ?? $shippingInfo['city'];
            $order->shipping_postal_code = $shippingInfo['postalCode'];
            $order->shipping_method = $shippingInfo['courier'] . ' ' . $shippingInfo['service'];
            $order->shipping_cost = $orderSummary['shipping'];
            $order->user = $request->user();
            
            // Get cart items
            $cart = $request->user()->cart;
            if (!$cart) {
                Log::warning('Cart not found for user', ['user_id' => $request->user()->id]);
                return response()->json([
                    'message' => 'Cart is empty',
                ], 422);
            }
            
            if ($cart->items->isEmpty()) {
                Log::warning('Cart is empty for user', ['user_id' => $request->user()->id]);
                return response()->json([
                    'message' => 'Cart is empty',
                ], 422);
            }
            
            Log::info('Cart items found', [
                'count' => $cart->items->count(),
                'first_item' => $cart->items->first() ? [
                    'id' => $cart->items->first()->id,
                    'product_id' => $cart->items->first()->product_id,
                    'has_product' => $cart->items->first()->product ? true : false
                ] : null
            ]);
            
            $order->items = $cart->items;
            
            // Get Snap Token
            Log::info('Requesting snap token from Midtrans service');
            $snapToken = $this->midtransService->getSnapToken($order);
            
            if (empty($snapToken)) {
                Log::error('Empty snap token returned from Midtrans service');
                return response()->json([
                    'message' => 'Failed to generate payment token',
                    'error' => 'Empty token received',
                ], 500);
            }
            
            Log::info('Payment token generated successfully', [
                'user_id' => $request->user()->id,
                'order_number' => $orderNumber,
                'token' => substr($snapToken, 0, 10) . '...'
            ]);
            
            return response()->json([
                'token' => $snapToken,
                'client_key' => Config::$clientKey,
                'order_number' => $orderNumber,
            ]);
        } catch (\Exception $e) {
            Log::error('Midtrans token error: ' . $e->getMessage(), [
                'user_id' => $request->user()->id,
                'exception_class' => get_class($e),
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Failed to generate payment token: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Process payment from Midtrans
     */
    public function processPayment(Request $request)
    {
        Log::info('Payment process request received', [
            'user_id' => $request->user()->id,
            'headers' => $request->headers->all()
        ]);
        
        $request->validate([
            'order_number' => 'required|string',
            'transaction_id' => 'required|string',
            'transaction_status' => 'required|string',
            'payment_type' => 'required|string',
            'shipping_info' => 'required|array',
            'order_summary' => 'required|array',
        ]);
        
        try {
            // Check if this transaction has already been processed
            $existingOrder = Order::where('midtrans_transaction_id', $request->transaction_id)
                ->orWhere('order_number', $request->order_number)
                ->first();
                
            if ($existingOrder) {
                Log::info('Payment already processed', [
                    'order_id' => $existingOrder->id,
                    'order_number' => $existingOrder->order_number,
                    'transaction_id' => $request->transaction_id
                ]);
                
                return response()->json([
                    'message' => 'Payment already processed',
                    'order' => $existingOrder->load(['items', 'trackingHistory']),
                    'already_processed' => true
                ]);
            }
            
            $user = $request->user();
            $cart = $user->cart;
            
            if (!$cart || $cart->items->isEmpty()) {
                Log::warning('Cart is empty for user', ['user_id' => $user->id]);
                return response()->json([
                    'message' => 'Cart is empty',
                ], 422);
            }
            
            // Determine order status based on transaction status
            $orderStatus = 'pending';
            $paymentStatus = 'pending';
            $paidAt = null;
            
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
            
            $shippingInfo = $request->shipping_info;
            $orderSummary = $request->order_summary;
            
            // Create order
            $order = Order::create([
                'order_number' => $request->order_number,
                'user_id' => $user->id,
                'subtotal' => $orderSummary['subtotal'],
                'shipping_cost' => $orderSummary['shipping'],
                'total_amount' => $orderSummary['total'],
                'status' => $orderStatus,
                'payment_status' => $paymentStatus,
                'payment_method' => $request->payment_type,
                'midtrans_transaction_id' => $request->transaction_id,
                'midtrans_response' => $request->all(),
                'shipping_method' => $shippingInfo['courier'] . ' ' . $shippingInfo['service'],
                'shipping_name' => $shippingInfo['name'],
                'shipping_phone' => $shippingInfo['phone'],
                'shipping_address' => $shippingInfo['address'],
                'shipping_city' => $shippingInfo['cityName'] ?? $shippingInfo['city'],
                'shipping_province' => $shippingInfo['provinceName'] ?? $shippingInfo['province'],
                'shipping_postal_code' => $shippingInfo['postalCode'],
                'notes' => $shippingInfo['notes'] ?? null,
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
                $product = $item->product;
                $stockBefore = $product->stock;
                $product->decrement('stock', $item->quantity);
                
                // Create stock history
                \App\Models\StockHistory::create([
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
                'status' => $orderStatus,
                'description' => $paymentStatus === 'paid' ? 'Payment received, order is being processed' : 'Order placed, waiting for payment',
            ]);
            
            // Clear cart
            $cart->items()->delete();
            $cart->update(['total_amount' => 0]);
            
            Log::info('Payment processed successfully', [
                'user_id' => $user->id,
                'order_id' => $order->id,
                'order_number' => $order->order_number
            ]);
            
            return response()->json([
                'message' => 'Payment processed successfully',
                'order' => $order->load(['items', 'trackingHistory']),
            ]);
        } catch (\Exception $e) {
            Log::error('Payment processing error: ' . $e->getMessage(), [
                'user_id' => $request->user()->id,
                'exception' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Failed to process payment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Handle webhook notification from Midtrans
     */
    public function webhook(Request $request)
    {
        Log::info('Midtrans webhook received', [
            'headers' => $request->headers->all(),
            'request_data' => $request->all()
        ]);

        try {
            // Check if this is a real Midtrans notification
            if (!$request->has('transaction_id') || !$request->has('order_id')) {
                Log::warning('Invalid webhook notification: Missing transaction_id or order_id');
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid notification format'
                ], 422);
            }

            // Get notification body
            $notificationBody = $request->all();
            
            // Process notification with Midtrans service
            $result = $this->midtransService->handleNotification($notificationBody);
            
            Log::info('Webhook processed successfully', [
                'order_id' => $notificationBody['order_id'],
                'transaction_id' => $notificationBody['transaction_id'],
                'status' => $notificationBody['transaction_status'] ?? 'unknown'
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Notification processed successfully',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Webhook error: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process notification: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Verify payment status
     */
    public function verifyPayment(Request $request)
    {
        try {
            $request->validate([
                'order_number' => 'required|string',
                'transaction_id' => 'required|string',
            ]);
            
            $orderNumber = $request->order_number;
            $transactionId = $request->transaction_id;
            
            Log::info('Payment verification request', [
                'user_id' => $request->user()->id,
                'order_number' => $orderNumber,
                'transaction_id' => $transactionId
            ]);
            
            // First, check if we already have an order with this transaction ID
            $order = Order::where('midtrans_transaction_id', $transactionId)
                ->orWhere('order_number', $orderNumber)
                ->first();
                
            if ($order) {
                // We have an order, check its payment status
                return response()->json([
                    'success' => true,
                    'verified' => true,
                    'status' => $order->payment_status,
                    'order_id' => $order->id,
                    'message' => 'Payment has been processed'
                ]);
            }
            
            // If no order found, check with Midtrans directly
            $statusCheck = $this->midtransService->checkStatus($orderNumber);
            
            if ($statusCheck['status'] === 'error') {
                Log::error('Error checking payment status with Midtrans', [
                    'order_number' => $orderNumber,
                    'transaction_id' => $transactionId,
                    'error' => $statusCheck['message']
                ]);
                
                return response()->json([
                    'success' => false,
                    'verified' => false,
                    'status' => 'unknown',
                    'message' => 'Could not verify payment status: ' . $statusCheck['message']
                ], 500);
            }
            
            $transactionStatus = $statusCheck['data']->transaction_status ?? 'unknown';
            $fraudStatus = $statusCheck['data']->fraud_status ?? null;
            
            // Determine payment status
            $paymentStatus = 'pending';
            $verified = false;
            
            if ($transactionStatus === 'capture' || $transactionStatus === 'settlement') {
                $paymentStatus = 'paid';
                $verified = true;
            } else if ($transactionStatus === 'pending') {
                $paymentStatus = 'pending';
                $verified = true;
            } else if (in_array($transactionStatus, ['deny', 'cancel', 'expire', 'failure'])) {
                $paymentStatus = 'failed';
                $verified = true;
            }
            
            Log::info('Payment verification result', [
                'order_number' => $orderNumber,
                'transaction_id' => $transactionId,
                'transaction_status' => $transactionStatus,
                'payment_status' => $paymentStatus,
                'verified' => $verified
            ]);
            
            return response()->json([
                'success' => true,
                'verified' => $verified,
                'status' => $paymentStatus,
                'transaction_status' => $transactionStatus,
                'fraud_status' => $fraudStatus,
                'message' => 'Payment status verified with Midtrans'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Exception in payment verification', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'verified' => false,
                'status' => 'error',
                'message' => 'Failed to verify payment: ' . $e->getMessage()
            ], 500);
        }
    }
} 