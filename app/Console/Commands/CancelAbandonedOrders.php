<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\StockHistory;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CancelAbandonedOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:cancel-abandoned';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancel orders that have been in pending payment status for more than 24 hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Looking for abandoned orders...');
        
        $cutoffTime = Carbon::now()->subHours(24);
        
        $abandonedOrders = Order::where('payment_status', 'pending')
            ->where('created_at', '<', $cutoffTime)
            ->whereNull('cancelled_at')
            ->get();
        
        $count = $abandonedOrders->count();
        
        if ($count === 0) {
            $this->info('No abandoned orders found.');
            return 0;
        }
        
        $this->info("Found {$count} abandoned orders. Cancelling...");
        
        $progressBar = $this->output->createProgressBar($count);
        $progressBar->start();
        
        foreach ($abandonedOrders as $order) {
            try {
                // Update order status
                $order->update([
                    'status' => 'cancelled',
                    'payment_status' => 'expired',
                    'cancelled_at' => Carbon::now(),
                ]);
                
                // Add tracking history
                $order->trackingHistory()->create([
                    'status' => 'cancelled',
                    'description' => 'Order automatically cancelled due to payment timeout',
                ]);
                
                // Restore stock
                foreach ($order->items as $item) {
                    $product = $item->product;
                    
                    if ($product) {
                        $stockBefore = $product->stock;
                        $product->increment('stock', $item->quantity);
                        
                        // Create stock history
                        StockHistory::create([
                            'product_id' => $product->id,
                            'quantity_change' => $item->quantity,
                            'stock_before' => $stockBefore,
                            'stock_after' => $product->stock,
                            'type' => 'in',
                            'reference_type' => 'order_cancel',
                            'reference_id' => $order->id,
                            'notes' => "Cancelled Order #{$order->order_number} (Abandoned)",
                            'created_by' => $order->user_id,
                        ]);
                    }
                }
                
                $progressBar->advance();
            } catch (\Exception $e) {
                Log::error("Error cancelling order #{$order->order_number}: " . $e->getMessage());
                $this->error("Error cancelling order #{$order->order_number}: " . $e->getMessage());
            }
        }
        
        $progressBar->finish();
        $this->newLine();
        $this->info("Successfully cancelled {$count} abandoned orders.");
        
        return 0;
    }
} 