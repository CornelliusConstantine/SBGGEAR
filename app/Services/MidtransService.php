<?php

namespace App\Services;

use App\Models\MidtransTransactionLog;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;
use Midtrans\Notification;

class MidtransService
{
    public function __construct()
    {
        // Configure Midtrans
        Config::$serverKey = env('MIDTRANS_SERVER_KEY', 'SB-Mid-server-LpZQd_jNi7NF9dMqdaDFFH95');
        Config::$clientKey = env('MIDTRANS_CLIENT_KEY', 'SB-Mid-client-NWRbGj3Ndj-152Ps');
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    /**
     * Generate Snap token for payment
     *
     * @param Order $order
     * @return string
     */
    public function getSnapToken(Order $order)
    {
        try {
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
                $params['item_details'][] = [
                    'id' => $item->product_id,
                    'price' => (int) $item->price,
                    'quantity' => $item->quantity,
                    'name' => $item->product_name,
                ];
            }

            // Add shipping cost to Midtrans parameters
            $params['item_details'][] = [
                'id' => 'SHIPPING',
                'price' => (int) $order->shipping_cost,
                'quantity' => 1,
                'name' => 'Shipping Cost (' . $order->shipping_method . ')',
            ];

            // Get Snap Token
            $snapToken = Snap::getSnapToken($params);
            
            // Log transaction
            $this->logTransaction($order->order_number, [
                'transaction_status' => 'pending',
                'gross_amount' => $order->total_amount,
                'raw_response' => $params
            ]);

            return $snapToken;
        } catch (\Exception $e) {
            Log::error('Midtrans error: ' . $e->getMessage());
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