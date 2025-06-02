<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;

class OrderController extends Controller
{
    /**
     * Display a listing of the user's orders
     *
     * @return \Illuminate\View\View
     */
    public function userOrders(Request $request)
    {
        $orders = $request->user()
            ->orders()
            ->with(['items.product'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('orders.index', compact('orders'));
    }

    /**
     * Display the specified order
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\View\View
     */
    public function show(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            abort(403, 'Unauthorized');
        }
        
        $order->load(['items.product', 'trackingHistory']);
        
        return view('orders.show', compact('order'));
    }
    
    /**
     * Display the order confirmation page
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\View\View
     */
    public function confirmation(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            abort(403, 'Unauthorized');
        }
        
        $order->load(['items.product']);
        
        return view('orders.confirmation', compact('order'));
    }
} 