<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }
    
    /**
     * Show the admin dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        return view('admin.dashboard');
    }
    
    /**
     * Show the orders index page.
     *
     * @return \Illuminate\View\View
     */
    public function orders()
    {
        return view('admin.orders.index');
    }
    
    /**
     * Show a specific order.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\View\View
     */
    public function showOrder(Order $order)
    {
        $order->load(['user', 'items.product', 'trackingHistory']);
        return view('admin.orders.show', compact('order'));
    }
    
    /**
     * Show the products index page.
     *
     * @return \Illuminate\View\View
     */
    public function products()
    {
        return view('admin.products.index');
    }
    
    /**
     * Show the users index page.
     *
     * @return \Illuminate\View\View
     */
    public function users()
    {
        return view('admin.users.index');
    }
} 