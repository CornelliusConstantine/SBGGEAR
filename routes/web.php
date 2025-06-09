<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\HomeController;

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

// For SPA, we don't need Laravel to handle the root route
// Route::get('/', function () {
//     return view('welcome');
// });

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
    
    // Order routes
    Route::get('/orders', [OrderController::class, 'userOrders'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/confirmation', [OrderController::class, 'confirmation'])->name('orders.confirmation');
    
    // Account routes
    Route::get('/account', [HomeController::class, 'account'])->name('account');
    Route::get('/account/edit', [HomeController::class, 'editAccount'])->name('account.edit');
    Route::put('/account', [HomeController::class, 'updateAccount'])->name('account.update');
});
