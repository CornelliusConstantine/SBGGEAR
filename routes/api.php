<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\StockController;
use App\Http\Controllers\Api\Admin\AdminController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Categories
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);

// Products
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/featured', [ProductController::class, 'featured']);
Route::get('/products/search/{query}', [ProductController::class, 'search']);
Route::get('/products/suggestions/{query}', [ProductController::class, 'suggestions']);
Route::get('/products/{product}', [ProductController::class, 'show']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // User profile
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/user', [AuthController::class, 'update']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Cart
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::put('/cart/{cartItem}', [CartController::class, 'update']);
    Route::delete('/cart/{cartItem}', [CartController::class, 'destroy']);
    
    // Orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::post('/orders/{order}/pay', [OrderController::class, 'pay']);
    Route::get('/orders/{order}/status', [OrderController::class, 'checkStatus']);
    
    // Product operations that require authentication
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);
    
    // Product comments
    Route::post('/products/{product}/comments', [ProductController::class, 'storeComment']);
    Route::post('/products/{product}/comments/{commentId}/reply', [ProductController::class, 'replyToComment']);

    // Admin routes
    Route::middleware('admin')->prefix('admin')->group(function () {
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::get('/dashboard/sales', [DashboardController::class, 'sales']);
        Route::get('/dashboard/products', [DashboardController::class, 'products']);
        
        // Categories management
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{category}', [CategoryController::class, 'update']);
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);
        
        // Products management
        Route::post('/products', [ProductController::class, 'store']);
        Route::post('/products/{product}', [ProductController::class, 'update']);
        Route::delete('/products/{product}', [ProductController::class, 'destroy']);
        
        // Product comments management (admin only)
        Route::delete('/products/{product}/comments/{commentId}/replies/{replyId}', [ProductController::class, 'deleteReply']);
        Route::delete('/products/{product}/comments/{commentId}', [ProductController::class, 'deleteComment']);
        
        // Stock management
        Route::get('/stock', [StockController::class, 'index']);
        Route::post('/stock/{product}/add', [StockController::class, 'addStock']);
        Route::post('/stock/{product}/reduce', [StockController::class, 'reduceStock']);
        Route::get('/stock/history', [StockController::class, 'history']);
        
        // Order management
        Route::get('/orders', [OrderController::class, 'adminIndex']);
        Route::put('/orders/{order}/status', [OrderController::class, 'updateStatus']);
        
        // Admin management
        Route::post('/flush-accounts', [AdminController::class, 'flushAdminAccounts']);
    });
});
