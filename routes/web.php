<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Admin\AdminController;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// API routes should be defined in api.php
// The frontend is handled by AngularJS

// Define our web routes first
Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

// Product routes
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/category/{category}', [ProductController::class, 'byCategory'])->name('products.category');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
Route::get('/new-arrivals', [ProductController::class, 'newArrivals'])->name('products.new');
Route::get('/on-sale', [ProductController::class, 'onSale'])->name('products.sale');

// Dedicated API endpoint for admin orders - Must come before SPA routes
Route::middleware(['auth', 'admin', 'json.response'])->group(function () {
    Route::get('/admin/api/get-orders', function () {
        try {
            $ordersQuery = \App\Models\Order::with(['user', 'items.product', 'trackingHistory'])
                ->orderBy('created_at', 'desc');
            
            // Default to showing only paid orders
            $ordersQuery->where('payment_status', 'paid');
            
            // Apply status filter if provided
            if (request()->has('status')) {
                $ordersQuery->where('status', request()->status);
            }
            
            $orders = $ordersQuery->get();
            
            \Illuminate\Support\Facades\Log::info('Admin API orders loaded', [
                'count' => $orders->count()
            ]);
            
            return response()->json([
                'data' => $orders,
                'count' => $orders->count()
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error loading admin orders', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'error' => 'Failed to load orders: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    });
    
    Route::get('/admin/api/get-order/{order}', function (\App\Models\Order $order) {
        try {
            $order->load(['user', 'items.product', 'trackingHistory']);
            
            \Illuminate\Support\Facades\Log::info('Admin API single order loaded', [
                'order_id' => $order->id
            ]);
            
            return response()->json([
                'data' => $order,
                'success' => true
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error loading single order', [
                'error' => $e->getMessage(),
                'order_id' => $order->id
            ]);
            
            return response()->json([
                'error' => 'Failed to load order: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    });

    Route::get('/admin/api/debug', function () {
        return response()->json([
            'message' => 'API is working',
            'user' => Auth::user() ? [
                'id' => Auth::user()->id,
                'email' => Auth::user()->email,
                'role' => Auth::user()->role,
                'is_admin' => Auth::user()->isAdmin()
            ] : 'Not authenticated'
        ]);
    });
});

// Protected routes (require authentication)
Route::middleware('auth')->group(function () {
    // Cart routes
    Route::get('/cart', [CartController::class, 'index'])->name('cart');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
    Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
    
    // Checkout routes
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
    Route::get('/checkout/shipping', [CheckoutController::class, 'shipping'])->name('checkout.shipping');
    Route::post('/checkout/shipping', [CheckoutController::class, 'processShipping'])->name('checkout.shipping.process');
    Route::get('/checkout/payment', [CheckoutController::class, 'payment'])->name('checkout.payment');
    Route::post('/checkout/payment', [CheckoutController::class, 'processPayment'])->name('checkout.payment.process');
    Route::get('/checkout/confirmation/{order}', [CheckoutController::class, 'confirmation'])->name('checkout.confirmation');
    Route::get('/checkout/confirmation', [CheckoutController::class, 'confirmationByOrderNumber'])->name('checkout.confirmation.by_number');
    Route::post('/checkout/calculate-shipping', [CheckoutController::class, 'calculateShipping'])->name('checkout.calculate-shipping');
    
    // Order routes - IMPORTANT: These routes should take precedence over the SPA routes
    // Comment out the Laravel route for orders to let Angular handle it
    // Route::get('/orders', [OrderController::class, 'userOrders'])->name('orders.index');
    Route::get('/orders/laravel', [OrderController::class, 'userOrders'])->name('orders.laravel');
    Route::get('/orders/redirect', [OrderController::class, 'redirect'])->name('orders.redirect');
    Route::get('/orders/{order}/laravel', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/confirmation', [OrderController::class, 'confirmation'])->name('orders.confirmation');
    
    // Account routes
    Route::get('/account', [HomeController::class, 'account'])->name('account');
    Route::get('/account/edit', [HomeController::class, 'editAccount'])->name('account.edit');
    Route::put('/account', [HomeController::class, 'updateAccount'])->name('account.update');
    
    // Admin routes
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/products', [AdminController::class, 'products'])->name('admin.products.index');
        Route::get('/users', [AdminController::class, 'users'])->name('admin.users.index');
        
        // Special direct web routes for admin dashboard data
        Route::get('/dashboard/stats', [AdminController::class, 'dashboardStats']);
        Route::get('/dashboard/recent-orders', [AdminController::class, 'recentOrders']);
        Route::get('/dashboard/top-products', [AdminController::class, 'topProducts']);
        Route::get('/dashboard/sales-chart', [AdminController::class, 'salesChart']);
        Route::get('/dashboard/low-stock-products', [AdminController::class, 'lowStockProducts']);
        
        // NEW: Direct route for orders data without using API
        Route::get('/get-orders', [AdminController::class, 'getOrders']);
        Route::get('/get-order/{order}', [AdminController::class, 'getOrder']);
        Route::put('/update-order-status/{order}', [AdminController::class, 'updateOrderStatus']);
        Route::put('/update-order-tracking/{order}', [AdminController::class, 'updateOrderTracking']);
        Route::put('/update-seat-receipt/{order}', [AdminController::class, 'updateSeatReceiptNumber']);
        
        // This needs to come after the API routes
        Route::get('/orders/{order}', [AdminController::class, 'showOrder'])->name('admin.orders.show');
    });
    
    // Admin orders route with proper middleware
    Route::middleware('admin')->get('/admin/orders', [AdminController::class, 'orders'])->name('admin.orders.index');
    
    // Special API routes accessible through web session
    Route::middleware(['auth', 'admin'])->group(function () {
        // Admin API routes proxied through web session
        Route::prefix('api/admin')->group(function () {
            Route::get('/orders', [App\Http\Controllers\Api\OrderController::class, 'adminIndex']);
            Route::get('/orders/{order}', [App\Http\Controllers\Api\OrderController::class, 'show']);
            Route::put('/orders/{order}/status', [App\Http\Controllers\Api\OrderController::class, 'updateStatus']);
            Route::put('/orders/{order}/tracking', [App\Http\Controllers\Api\OrderController::class, 'updateTracking']);
            
            // Add more admin API routes as needed
            Route::get('/dashboard/stats', [App\Http\Controllers\Api\Admin\DashboardController::class, 'stats']);
            Route::get('/dashboard/recent-orders', [App\Http\Controllers\Api\Admin\DashboardController::class, 'recentOrders']);
            Route::get('/dashboard/top-products', [App\Http\Controllers\Api\Admin\DashboardController::class, 'topProducts']);
            Route::get('/dashboard/sales-chart', [App\Http\Controllers\Api\Admin\DashboardController::class, 'salesChart']);
            Route::get('/dashboard/low-stock-products', [App\Http\Controllers\Api\Admin\DashboardController::class, 'lowStockProducts']);
            
            // Direct API routes for adminOrderController.js - Using web middleware explicitly
            Route::get('/direct/orders', [App\Http\Controllers\Api\AdminApiController::class, 'getOrders'])
                ->middleware(['web', 'auth', 'admin', 'json.response']);
            Route::get('/direct/orders/{order}', [App\Http\Controllers\Api\AdminApiController::class, 'getOrder'])
                ->middleware(['web', 'auth', 'admin', 'json.response']);
        });
        
        // Special direct web API route for admin orders
        Route::prefix('admin')->group(function () {
            Route::get('/api/orders', [App\Http\Controllers\Api\OrderController::class, 'adminIndex']);
            Route::get('/api/orders/{order}', [App\Http\Controllers\Api\OrderController::class, 'show']);
            Route::put('/api/orders/{order}/status', [App\Http\Controllers\Api\OrderController::class, 'updateStatus']);
            Route::put('/api/orders/{order}/tracking', [App\Http\Controllers\Api\OrderController::class, 'updateTracking']);
            Route::put('/api/orders/{order}/seat-receipt', [App\Http\Controllers\Api\OrderController::class, 'updateSeatReceipt']);
        });
    });
});

// Midtrans webhook
Route::post('/payment/webhook', [CheckoutController::class, 'webhook'])->name('payment.webhook');

// Authentication test route
Route::get('/test-auth', function() {
    return response()->json([
        'authenticated' => auth()->check(),
        'user' => auth()->check() ? [
            'id' => auth()->user()->id,
            'email' => auth()->user()->email,
            'role' => auth()->user()->role,
            'is_admin' => auth()->user()->isAdmin()
        ] : null,
        'session_id' => session()->getId(),
        'session_status' => session()->isStarted() ? 'started' : 'not started'
    ]);
});

// Fix admin status when needed
Route::get('/fix-admin', function() {
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->email === 'admin@example.com' && $user->role !== 'admin') {
            $user->role = 'admin';
            $user->save();
            return response()->json([
                'success' => true,
                'message' => 'Admin status fixed',
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'role' => $user->role,
                    'is_admin' => $user->isAdmin()
                ]
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => 'User is not meant to be admin or already has admin role',
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'is_admin' => $user->isAdmin()
            ]
        ]);
    }
    return response()->json([
        'success' => false,
        'message' => 'Not authenticated'
    ], 401);
});

// Create admin and login directly
Route::get('/create-admin-login', function() {
    // Check if admin user exists
    $admin = \App\Models\User::where('email', 'admin@example.com')->first();
    
    if (!$admin) {
        // Create admin user with explicit admin role
        $admin = \App\Models\User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'admin',
        ]);
        
        \Illuminate\Support\Facades\Log::info('Created admin user', ['user_id' => $admin->id]);
    } else {
        // Ensure user has admin role
        if ($admin->role !== 'admin') {
            $admin->role = 'admin';
            $admin->save();
            \Illuminate\Support\Facades\Log::info('Updated user to admin role', ['user_id' => $admin->id]);
        }
    }
    
    // Logout current user if any
    \Illuminate\Support\Facades\Auth::logout();
    
    // Login as admin
    \Illuminate\Support\Facades\Auth::login($admin);
    
    \Illuminate\Support\Facades\Log::info('Logged in as admin', [
        'user_id' => $admin->id,
        'authenticated' => \Illuminate\Support\Facades\Auth::check(),
        'user_role' => $admin->role,
        'is_admin' => $admin->isAdmin()
    ]);
    
    // Redirect to admin page with JavaScript to set sessionStorage
    return response()->view('admin.redirect', [
        'redirect_url' => '/admin/orders',
        'message' => 'Logged in as admin successfully'
    ]);
});

// Midtrans test route (only for development)
if (app()->environment('local')) {
    Route::get('/test-midtrans', [App\Http\Controllers\TestMidtransController::class, 'index'])->name('test.midtrans');
    
    // Testing route to create and login as admin (only for development)
    Route::get('/test-admin-login', function() {
        // Check if admin user exists
        $admin = \App\Models\User::where('email', 'admin@example.com')->first();
        
        if (!$admin) {
            // Create admin user
            $admin = \App\Models\User::create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => 'admin',
            ]);
            
            \Illuminate\Support\Facades\Log::info('Created admin user', ['user_id' => $admin->id]);
        }
        
        // Login as admin
        \Illuminate\Support\Facades\Auth::login($admin);
        
        \Illuminate\Support\Facades\Log::info('Logged in as admin', [
            'user_id' => $admin->id,
            'authenticated' => \Illuminate\Support\Facades\Auth::check(),
            'user_role' => $admin->role
        ]);
        
        // Redirect to admin orders
        return redirect('/admin/orders')->with('success', 'Logged in as admin');
    });
}

// SPA route - Angular will handle routing on the frontend
// IMPORTANT: This route must come AFTER all other specific routes
Route::get('/', function () {
    return view('spa');
});

// SPA fallback route - All routes not matched by Laravel will be handled by Angular
// IMPORTANT: This route must be the LAST route defined
Route::get('/{any}', function () {
    return view('spa');
})->where('any', '^(?!api|account|cart|checkout|products|home|login|register|password|admin).*$');

// Route for SPA to get CSRF token
Route::get('/csrf-token', function () {
    return response()->json([
        'token' => csrf_token()
    ]);
});
