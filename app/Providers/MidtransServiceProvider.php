<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Midtrans\Config;
use Illuminate\Support\Facades\Log;

class MidtransServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        try {
            // Get configuration values
            $serverKey = config('midtrans.server_key');
            $clientKey = config('midtrans.client_key');
            $isProduction = config('midtrans.is_production', false);
            
            // Log configuration for debugging
            Log::info('Configuring Midtrans service', [
                'server_key' => $serverKey ? substr($serverKey, 0, 10) . '...' : 'not set',
                'client_key' => $clientKey ? substr($clientKey, 0, 10) . '...' : 'not set',
                'is_production' => $isProduction ? 'true' : 'false',
                'merchant_id' => config('midtrans.merchant_id') ?? 'not set'
            ]);
            
            // Validate essential configuration
            if (empty($serverKey)) {
                Log::error('Midtrans server key is not configured');
            }
            
            if (empty($clientKey)) {
                Log::error('Midtrans client key is not configured');
            }
            
            // Configure Midtrans globally
            Config::$serverKey = $serverKey;
            Config::$clientKey = $clientKey;
            Config::$isProduction = $isProduction;
            Config::$isSanitized = config('midtrans.is_sanitized', true);
            Config::$is3ds = config('midtrans.is_3ds', true);
            
            // Set notification URL for webhooks
            try {
                Config::$appendNotifUrl = route('api.payment.webhook');
                Config::$overrideNotifUrl = route('api.payment.webhook');
            } catch (\Exception $e) {
                // Route might not be available during some console commands
                // This prevents errors during migrations, etc.
                Log::warning('Could not set Midtrans notification URL: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            Log::error('Error configuring Midtrans: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
} 