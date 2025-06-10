<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Midtrans\Config;

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
        // Configure Midtrans globally
        Config::$serverKey = config('midtrans.server_key');
        Config::$clientKey = config('midtrans.client_key');
        Config::$isProduction = config('midtrans.is_production', false);
        Config::$isSanitized = config('midtrans.is_sanitized', true);
        Config::$is3ds = config('midtrans.is_3ds', true);
        
        // Set notification URL for webhooks
        try {
            Config::$appendNotifUrl = route('api.payment.webhook');
            Config::$overrideNotifUrl = route('api.payment.webhook');
        } catch (\Exception $e) {
            // Route might not be available during some console commands
            // This prevents errors during migrations, etc.
        }
    }
} 