<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminApiController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth', 'admin', 'json.response']);
    }
    
    /**
     * Get all orders for admin dashboard
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrders(Request $request)
    {
        try {
            // Debug authentication info
            Log::info('Admin API Auth Debug', [
                'user_authenticated' => auth()->check(),
                'user_id' => auth()->check() ? auth()->user()->id : null,
                'user_email' => auth()->check() ? auth()->user()->email : null,
                'user_role' => auth()->check() ? auth()->user()->role : null,
                'is_admin' => auth()->check() ? (auth()->user()->isAdmin() ? 'true' : 'false') : 'user not logged in',
                'session_id' => session()->getId(),
                'request_headers' => $request->headers->all()
            ]);
            
            // Explicitly check admin permissions
            if (!auth()->check()) {
                Log::warning('Unauthorized access attempt - user not authenticated');
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            
            if (!auth()->user()->isAdmin()) {
                Log::warning('Unauthorized access attempt - user not admin', [
                    'user_id' => auth()->user()->id,
                    'user_role' => auth()->user()->role
                ]);
                return response()->json(['message' => 'Unauthorized access.'], 403);
            }
            
            // Start query with relationships
            $ordersQuery = Order::with(['user', 'items.product', 'trackingHistory'])
                ->orderBy('created_at', 'desc');
            
            // Default to showing only paid orders unless explicitly requested otherwise
            if ($request->has('payment_status')) {
                $ordersQuery->where('payment_status', $request->payment_status);
            } else {
                $ordersQuery->where('payment_status', 'paid');
            }
            
            // Apply other filters
            if ($request->has('status')) {
                $ordersQuery->where('status', $request->status);
            }
            
            if ($request->has('date_from')) {
                $ordersQuery->whereDate('created_at', '>=', $request->date_from);
            }
            
            if ($request->has('date_to')) {
                $ordersQuery->whereDate('created_at', '<=', $request->date_to);
            }
            
            // Get all orders without pagination to avoid duplicate issues
            $orders = $ordersQuery->get();
            
            // Log the orders for debugging
            Log::info('Admin API orders loaded', [
                'count' => $orders->count(),
                'filters' => $request->all(),
                'first_few_ids' => $orders->take(5)->pluck('id')->toArray()
            ]);
            
            return response()->json([
                'data' => $orders,
                'count' => $orders->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading admin orders', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Failed to load orders: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }
    
    /**
     * Get a specific order
     * 
     * @param \App\Models\Order $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrder(Order $order)
    {
        try {
            $order->load(['user', 'items.product', 'trackingHistory']);
            
            Log::info('Admin API single order loaded', [
                'order_id' => $order->id
            ]);
            
            return response()->json([
                'data' => $order,
                'success' => true
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading single order', [
                'error' => $e->getMessage(),
                'order_id' => $order->id
            ]);
            
            return response()->json([
                'error' => 'Failed to load order: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
} 