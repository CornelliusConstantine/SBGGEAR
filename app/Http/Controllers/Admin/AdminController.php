<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
     * Display admin orders management page.
     *
     * @return \Illuminate\View\View
     */
    public function orders()
    {
        // Debug user authentication status
        \Illuminate\Support\Facades\Log::info('Admin orders page accessed', [
            'user_id' => auth()->id(),
            'user_email' => auth()->user()->email,
            'user_role' => auth()->user()->role,
            'is_admin' => auth()->user()->isAdmin(),
            'method' => 'AdminController@orders'
        ]);
        
        // Return the simple admin orders view
        return view('admin.orders');
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
    
    /**
     * Get dashboard statistics.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function dashboardStats()
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        
        // Get revenue for current month
        $revenueThisMonth = Order::where('payment_status', 'paid')
            ->whereDate('created_at', '>=', $startOfMonth)
            ->sum('total_amount');
            
        // Get order count for current month
        $ordersThisMonth = Order::whereDate('created_at', '>=', $startOfMonth)
            ->count();
            
        // Get total products
        $totalProducts = Product::count();
        
        // Get total customers
        $totalCustomers = User::where('role', '!=', 'admin')->count();
        
        return response()->json([
            'revenue_this_month' => $revenueThisMonth,
            'orders_this_month' => $ordersThisMonth,
            'total_products' => $totalProducts,
            'total_customers' => $totalCustomers
        ]);
    }
    
    /**
     * Get recent orders.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function recentOrders()
    {
        $recentOrders = Order::with('user')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
            
        return response()->json($recentOrders);
    }
    
    /**
     * Get top products.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function topProducts()
    {
        $topProducts = DB::table('order_items')
            ->select('product_id', 'product_name', DB::raw('SUM(quantity) as total_sold'))
            ->groupBy('product_id', 'product_name')
            ->orderBy('total_sold', 'desc')
            ->take(5)
            ->get();
            
        return response()->json($topProducts);
    }
    
    /**
     * Get sales chart data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function salesChart(Request $request)
    {
        $period = $request->input('period', 'daily');
        
        if ($period === 'daily') {
            // Get daily sales for the last 30 days
            $startDate = Carbon::now()->subDays(30);
            $sales = DB::table('orders')
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('SUM(total_amount) as total_sales')
                )
                ->where('payment_status', 'paid')
                ->where('created_at', '>=', $startDate)
                ->groupBy('date')
                ->orderBy('date')
                ->get();
        } else {
            // Get monthly sales for the last 12 months
            $startDate = Carbon::now()->subMonths(12);
            $sales = DB::table('orders')
                ->select(
                    DB::raw('YEAR(created_at) as year'),
                    DB::raw('MONTH(created_at) as month'),
                    DB::raw('SUM(total_amount) as total_sales')
                )
                ->where('payment_status', 'paid')
                ->where('created_at', '>=', $startDate)
                ->groupBy('year', 'month')
                ->orderBy('year')
                ->orderBy('month')
                ->get();
        }
        
        return response()->json($sales);
    }
    
    /**
     * Get low stock products.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function lowStockProducts()
    {
        $lowStockProducts = Product::where('stock', '<=', 10)
            ->orderBy('stock')
            ->take(5)
            ->get();
            
        return response()->json($lowStockProducts);
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
            \Illuminate\Support\Facades\Log::info('Admin orders loaded', [
                'count' => $orders->count(),
                'filters' => $request->all(),
                'first_few_ids' => $orders->take(5)->pluck('id')->toArray()
            ]);
            
            // Check if we have any orders
            if ($orders->isEmpty()) {
                \Illuminate\Support\Facades\Log::info('No orders found matching criteria');
                
                // Return empty array explicitly wrapped in a data property to avoid confusion
                return response()->json([
                    'data' => [],
                    'message' => 'No orders found matching the criteria',
                    'count' => 0
                ]);
            }
            
            // Always wrap the orders in a data property to ensure consistent format
            return response()->json([
                'data' => $orders,
                'count' => $orders->count()
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error loading admin orders', [
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
        $order->load(['user', 'items.product', 'trackingHistory']);
        return response()->json($order);
    }
    
    /**
     * Update order status
     * 
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Order $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateOrderStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|string|in:pending,processing,shipped,delivered,cancelled',
        ]);

        $order->status = $request->status;
        $order->save();

        // Add to tracking history
        $order->trackingHistory()->create([
            'status' => $request->status,
            'description' => "Order status updated to {$request->status}",
        ]);

        return response()->json([
            'message' => 'Order status updated successfully',
            'order' => $order->load(['items.product', 'trackingHistory']),
        ]);
    }
    
    /**
     * Update order tracking number
     * 
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Order $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateOrderTracking(Request $request, Order $order)
    {
        $request->validate([
            'tracking_number' => 'required|string',
        ]);

        $order->tracking_number = $request->tracking_number;
        $order->save();

        // Add to tracking history
        $order->trackingHistory()->create([
            'status' => $order->status,
            'description' => "Tracking number updated to {$request->tracking_number}",
        ]);

        return response()->json([
            'message' => 'Tracking number updated successfully',
            'order' => $order->load(['items.product', 'trackingHistory']),
        ]);
    }

    /**
     * Update order seat receipt number
     * 
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Order $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSeatReceiptNumber(Request $request, Order $order)
    {
        $request->validate([
            'seat_receipt_number' => 'required|string',
        ]);

        $order->seat_receipt_number = $request->seat_receipt_number;
        
        // If we're setting a seat receipt, update the status to processing if it's still pending
        if ($order->status === 'pending') {
            $order->status = 'processing';
        }
        
        $order->save();

        // Add to tracking history
        $order->trackingHistory()->create([
            'status' => $order->status,
            'description' => "Seat receipt number updated to {$request->seat_receipt_number}",
        ]);

        return response()->json([
            'message' => 'Seat receipt number updated successfully',
            'order' => $order->load(['items.product', 'trackingHistory']),
        ]);
    }
} 