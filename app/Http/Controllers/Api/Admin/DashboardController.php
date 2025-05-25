<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats()
    {
        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();

        $stats = [
            'total_orders' => Order::count(),
            'orders_today' => Order::whereDate('created_at', $today)->count(),
            'orders_this_month' => Order::whereMonth('created_at', $thisMonth->month)
                ->whereYear('created_at', $thisMonth->year)
                ->count(),
            'revenue_today' => Order::whereDate('created_at', $today)
                ->where('payment_status', 'paid')
                ->sum('total_amount'),
            'revenue_this_month' => Order::whereMonth('created_at', $thisMonth->month)
                ->whereYear('created_at', $thisMonth->year)
                ->where('payment_status', 'paid')
                ->sum('total_amount'),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'processing_orders' => Order::where('status', 'processing')->count(),
            'shipped_orders' => Order::where('status', 'shipped')->count(),
            'completed_orders' => Order::where('status', 'delivered')->count(),
            'low_stock_products' => Product::where('stock', '<=', 10)->count(),
            'out_of_stock_products' => Product::where('stock', 0)->count(),
        ];

        return response()->json($stats);
    }

    public function recentOrders()
    {
        $orders = Order::with(['user:id,name,email', 'items.product:id,name'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return response()->json($orders);
    }

    public function topProducts()
    {
        $topProducts = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.payment_status', 'paid')
            ->select(
                'products.id',
                'products.name',
                'products.stock',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.subtotal) as total_revenue')
            )
            ->groupBy('products.id', 'products.name', 'products.stock')
            ->orderBy('total_quantity', 'desc')
            ->take(10)
            ->get();

        return response()->json($topProducts);
    }

    public function salesChart(Request $request)
    {
        $request->validate([
            'period' => ['required', 'in:daily,monthly'],
        ]);

        if ($request->period === 'daily') {
            $sales = Order::where('payment_status', 'paid')
                ->whereBetween('created_at', [now()->subDays(30), now()])
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as total_orders'),
                    DB::raw('SUM(total_amount) as total_sales')
                )
                ->groupBy('date')
                ->orderBy('date')
                ->get();
        } else {
            $sales = Order::where('payment_status', 'paid')
                ->whereBetween('created_at', [now()->subMonths(12), now()])
                ->select(
                    DB::raw('YEAR(created_at) as year'),
                    DB::raw('MONTH(created_at) as month'),
                    DB::raw('COUNT(*) as total_orders'),
                    DB::raw('SUM(total_amount) as total_sales')
                )
                ->groupBy('year', 'month')
                ->orderBy('year')
                ->orderBy('month')
                ->get();
        }

        return response()->json($sales);
    }
} 