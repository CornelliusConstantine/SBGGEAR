<?php

require __DIR__ . '/vendor/autoload.php';

use Midtrans\Config;
use Midtrans\Snap;

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing Midtrans Configuration...\n";

// Set your Midtrans server key
Config::$serverKey = 'SB-Mid-server-LpZQd_jNi7NF9dMqdaDFFH95';
echo "Server Key: " . substr(Config::$serverKey, 0, 10) . "...\n";

// Set to Development/Sandbox Environment (default). Set to true for Production Environment (accept real transaction).
Config::$isProduction = false;
echo "Is Production: " . (Config::$isProduction ? 'true' : 'false') . "\n";

// Set sanitization on (default)
Config::$isSanitized = true;
// Set 3DS transaction for credit card to true
Config::$is3ds = true;

$params = array(
    'transaction_details' => array(
        'order_id' => 'TEST-' . rand(),
        'gross_amount' => 10000,
    ),
    'customer_details' => array(
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test@example.com',
        'phone' => '08111222333',
    ),
);

echo "Request Parameters: " . json_encode($params, JSON_PRETTY_PRINT) . "\n";

try {
    echo "Attempting to get Snap Token...\n";
    // Get Snap Token
    $snapToken = Snap::getSnapToken($params);
    echo "SUCCESS: Got snap token - " . $snapToken . "\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
} 