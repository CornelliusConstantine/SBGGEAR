<?php

namespace App\Services;

use App\Models\MidtransTransactionLog;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;
use Midtrans\Notification;
use Illuminate\Support\Facades\Route;

class MidtransService
{
    public function __construct()
    {
        // Configuration is handled by MidtransServiceProvider
        // This ensures Midtrans is configured before any service methods are called
    }

    /**
     * Generate Snap token for payment
     *
     * @param mixed $order Order object or stdClass with required properties
     * @return string
     */
    public function getSnapToken($order)
    {
        try {
            // First, verify that Midtrans is properly configured
            if (empty(Config::$serverKey)) {
                Log::error('Midtrans configuration error: Server key is empty');
                throw new \Exception('Midtrans server key is not configured');
            }

            $params = [
                'transaction_details' => [
                    'order_id' => $order->order_number,
                    'gross_amount' => (int) $order->total_amount,
                ],
                'customer_details' => [
                    'first_name' => $order->shipping_name,
                    'email' => $order->user->email,
                    'phone' => $order->shipping_phone,
                    'shipping_address' => [
                        'first_name' => $order->shipping_name,
                        'phone' => $order->shipping_phone,
                        'address' => $order->shipping_address,
                        'city' => $order->shipping_city,
                        'postal_code' => $order->shipping_postal_code,
                        'country_code' => 'IDN',
                    ],
                ],
                'item_details' => []
            ];

            // Add order items to Midtrans parameters
            foreach ($order->items as $item) {
                $itemName = '';
                if (isset($item->product_name)) {
                    $itemName = $item->product_name;
                } elseif (isset($item->product) && $item->product) {
                    $itemName = $item->product->name;
                } else {
                    $itemName = 'Product #' . $item->product_id;
                }
                
                $params['item_details'][] = [
                    'id' => $item->product_id,
                    'price' => (int) $item->price,
                    'quantity' => $item->quantity,
                    'name' => $itemName,
                ];
            }

            // Add shipping cost to Midtrans parameters
            $params['item_details'][] = [
                'id' => 'SHIPPING',
                'price' => (int) $order->shipping_cost,
                'quantity' => 1,
                'name' => 'Shipping Cost (' . $order->shipping_method . ')',
            ];

            // Log request parameters for debugging
            Log::info('Midtrans Snap Token Request', [
                'order_number' => $order->order_number,
                'params' => $params,
                'server_key' => Config::$serverKey ? substr(Config::$serverKey, 0, 10) . '...' : 'not set',
                'is_production' => Config::$isProduction ? 'true' : 'false',
                'client_key' => Config::$clientKey ? substr(Config::$clientKey, 0, 10) . '...' : 'not set',
                'api_endpoint' => Config::$isProduction ? 'https://app.midtrans.com' : 'https://app.sandbox.midtrans.com'
            ]);

            // Get Snap Token
            $snapToken = Snap::getSnapToken($params);
            
            if (empty($snapToken)) {
                Log::error('Empty snap token returned from Midtrans');
                throw new \Exception('Failed to generate Midtrans snap token (empty response)');
            }
            
            // Log success
            Log::info('Midtrans Snap Token generated successfully', [
                'order_number' => $order->order_number,
                'token' => substr($snapToken, 0, 10) . '...'
            ]);
            
            // Log transaction
            $this->logTransaction($order->order_number, [
                'transaction_status' => 'pending',
                'gross_amount' => $order->total_amount,
                'raw_response' => $params
            ]);

            return $snapToken;
        } catch (\Exception $e) {
            Log::error('Midtrans error: ' . $e->getMessage(), [
                'order_number' => $order->order_number ?? 'unknown',
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Handle webhook notification from Midtrans
     *
     * @param array $notificationBody
     * @return array
     */
    public function handleNotification($notificationBody)
    {
        try {
            // Log the notification
            $this->logTransaction($notificationBody['order_id'], [
                'transaction_id' => $notificationBody['transaction_id'] ?? null,
                'transaction_status' => $notificationBody['transaction_status'] ?? null,
                'payment_type' => $notificationBody['payment_type'] ?? null,
                'fraud_status' => $notificationBody['fraud_status'] ?? null,
                'gross_amount' => $notificationBody['gross_amount'] ?? null,
                'raw_response' => $notificationBody
            ]);

            // Find order
            $order = Order::where('order_number', $notificationBody['order_id'])->first();
            
            if (!$order) {
                return [
                    'status' => 'error',
                    'message' => 'Order not found'
                ];
            }

            // Update order based on transaction status
            switch ($notificationBody['transaction_status']) {
                case 'capture':
                case 'settlement':
                    $order->update([
                        'status' => 'processing',
                        'payment_status' => 'paid',
                        'midtrans_transaction_id' => $notificationBody['transaction_id'] ?? null,
                        'midtrans_response' => $notificationBody,
                        'paid_at' => now(),
                    ]);
                    
                    // Add tracking history
                    $order->trackingHistory()->create([
                        'status' => 'processing',
                        'description' => 'Payment confirmed',
                    ]);
                    break;
                    
                case 'pending':
                    $order->update([
                        'payment_status' => 'pending',
                        'midtrans_transaction_id' => $notificationBody['transaction_id'] ?? null,
                        'midtrans_response' => $notificationBody,
                    ]);
                    break;
                    
                case 'deny':
                case 'cancel':
                case 'expire':
                    $order->update([
                        'status' => 'cancelled',
                        'payment_status' => 'failed',
                        'midtrans_transaction_id' => $notificationBody['transaction_id'] ?? null,
                        'midtrans_response' => $notificationBody,
                        'cancelled_at' => now(),
                    ]);
                    
                    // Add tracking history
                    $order->trackingHistory()->create([
                        'status' => 'cancelled',
                        'description' => 'Payment ' . $notificationBody['transaction_status'],
                    ]);
                    
                    // Restore stock
                    foreach ($order->items as $item) {
                        $product = $item->product;
                        if ($product) {
                            $stockBefore = $product->stock;
                            $product->increment('stock', $item->quantity);
                            
                            // Create stock history
                            \App\Models\StockHistory::create([
                                'product_id' => $product->id,
                                'quantity_change' => $item->quantity,
                                'stock_before' => $stockBefore,
                                'stock_after' => $product->stock,
                                'type' => 'in',
                                'reference_type' => 'order_cancel',
                                'reference_id' => $order->id,
                                'notes' => "Cancelled Order #{$order->order_number}",
                                'created_by' => $order->user_id,
                            ]);
                        }
                    }
                    break;
            }

            return [
                'status' => 'success',
                'order' => $order
            ];
        } catch (\Exception $e) {
            Log::error('Midtrans notification error: ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Check transaction status
     *
     * @param string $orderId
     * @return array
     */
    public function checkStatus($orderId)
    {
        try {
            $status = Transaction::status($orderId);
            
            // Log the status check
            $this->logTransaction($orderId, [
                'transaction_id' => $status->transaction_id ?? null,
                'transaction_status' => $status->transaction_status ?? null,
                'payment_type' => $status->payment_type ?? null,
                'fraud_status' => $status->fraud_status ?? null,
                'gross_amount' => $status->gross_amount ?? null,
                'raw_response' => (array) $status
            ]);

            return [
                'status' => 'success',
                'data' => $status
            ];
        } catch (\Exception $e) {
            Log::error('Midtrans status check error: ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Log transaction
     *
     * @param string $orderNumber
     * @param array $data
     * @return MidtransTransactionLog
     */
    private function logTransaction($orderNumber, $data)
    {
        return MidtransTransactionLog::create(array_merge(
            ['order_number' => $orderNumber],
            $data
        ));
    }
} 