<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CheckoutController;

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
});

// Midtrans webhook
Route::post('/payment/webhook', [CheckoutController::class, 'webhook'])->name('payment.webhook');

// Midtrans test route (only for development)
if (app()->environment('local')) {
    Route::get('/test-midtrans', [App\Http\Controllers\TestMidtransController::class, 'index'])->name('test.midtrans');
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
})->where('any', '^(?!api|account|cart|checkout|products|home|login|register|password).*$');
