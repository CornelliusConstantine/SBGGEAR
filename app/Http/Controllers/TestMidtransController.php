<?php

namespace App\Http\Controllers;

use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TestMidtransController extends Controller
{
    protected $midtransService;
    
    public function __construct(MidtransService $midtransService)
    {
        $this->middleware('auth');
        $this->midtransService = $midtransService;
    }
    
    /**
     * Show test page for Midtrans integration
     */
    public function index()
    {
        $user = Auth::user();
        
        // Create test order data
        $orderNumber = 'TEST-' . date('Ymd') . '-' . strtoupper(Str::random(5));
        $amount = 10000; // IDR 10,000
        $customerName = $user->name;
        
        // Create a mock order object for Midtrans
        $order = new \stdClass();
        $order->order_number = $orderNumber;
        $order->total_amount = $amount;
        $order->shipping_name = $customerName;
        $order->shipping_phone = '081234567890';
        $order->shipping_address = 'Test Address';
        $order->shipping_city = 'Jakarta';
        $order->shipping_postal_code = '12345';
        $order->user = $user;
        $order->items = collect([
            (object)[
                'product_id' => 'TEST-PRODUCT',
                'price' => $amount,
                'quantity' => 1,
                'product_name' => 'Test Product'
            ]
        ]);
        
        try {
            // Get Snap Token from Midtrans service
            $snapToken = $this->midtransService->getSnapToken($order);
            
            return view('test-midtrans', compact('orderNumber', 'amount', 'customerName', 'snapToken'));
        } catch (\Exception $e) {
            return back()->with('error', 'Midtrans error: ' . $e->getMessage());
        }
    }
} 