<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StatsDummyController extends Controller
{
    public function stats()
    {
        $stats = [
            'total_orders' => 145,
            'orders_today' => 5,
            'orders_this_month' => 45,
            'revenue_today' => 2500000,
            'revenue_this_month' => 12500000,
            'pending_orders' => 10,
            'processing_orders' => 15,
            'shipped_orders' => 12,
            'completed_orders' => 8,
            'low_stock_products' => 5,
            'out_of_stock_products' => 2,
            'total_products' => 120,
            'total_customers' => 89,
        ];

        return response()->json($stats);
    }

    public function recentOrders()
    {
        $orders = [
            [
                'id' => 1,
                'order_number' => 'ORD-2023-001',
                'created_at' => '2023-06-15T08:30:00',
                'user' => [
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                ],
                'total_amount' => 750000,
                'status' => 'delivered',
                'payment_status' => 'paid',
            ],
            [
                'id' => 2,
                'order_number' => 'ORD-2023-002',
                'created_at' => '2023-06-16T10:15:00',
                'user' => [
                    'name' => 'Jane Smith',
                    'email' => 'jane@example.com',
                ],
                'total_amount' => 1250000,
                'status' => 'processing',
                'payment_status' => 'paid',
            ],
            [
                'id' => 3,
                'order_number' => 'ORD-2023-003',
                'created_at' => '2023-06-16T14:45:00',
                'user' => [
                    'name' => 'Robert Johnson',
                    'email' => 'robert@example.com',
                ],
                'total_amount' => 500000,
                'status' => 'pending',
                'payment_status' => 'unpaid',
            ],
            [
                'id' => 4,
                'order_number' => 'ORD-2023-004',
                'created_at' => '2023-06-17T09:20:00',
                'user' => [
                    'name' => 'Emily Williams',
                    'email' => 'emily@example.com',
                ],
                'total_amount' => 1800000,
                'status' => 'shipped',
                'payment_status' => 'paid',
            ],
            [
                'id' => 5,
                'order_number' => 'ORD-2023-005',
                'created_at' => '2023-06-17T16:30:00',
                'user' => [
                    'name' => 'Michael Brown',
                    'email' => 'michael@example.com',
                ],
                'total_amount' => 950000,
                'status' => 'cancelled',
                'payment_status' => 'refunded',
            ],
        ];

        return response()->json($orders);
    }

    public function lowStockProducts()
    {
        $products = [
            [
                'id' => 1,
                'name' => 'Safety Helmet Type A',
                'sku' => 'HEA-1234',
                'stock' => 3,
                'category' => [
                    'id' => 1,
                    'name' => 'Head Protection'
                ]
            ],
            [
                'id' => 2,
                'name' => 'Safety Goggles',
                'sku' => 'EYE-5678',
                'stock' => 5,
                'category' => [
                    'id' => 2,
                    'name' => 'Eye Protection'
                ]
            ],
            [
                'id' => 3,
                'name' => 'Reflective Vest XL',
                'sku' => 'VIS-9012',
                'stock' => 2,
                'category' => [
                    'id' => 5,
                    'name' => 'Visibility'
                ]
            ]
        ];

        return response()->json($products);
    }

    public function salesChart(Request $request)
    {
        $period = $request->input('period', 'daily');
        
        if ($period === 'daily') {
            $data = [];
            // Generate 30 days of data
            for ($i = 30; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-$i days"));
                $data[] = [
                    'date' => $date,
                    'total_orders' => rand(0, 5),
                    'total_sales' => rand(100000, 1000000)
                ];
            }
        } else {
            $data = [];
            // Generate 12 months of data
            for ($i = 12; $i >= 0; $i--) {
                $yearMonth = date('Y-m', strtotime("-$i months"));
                list($year, $month) = explode('-', $yearMonth);
                $data[] = [
                    'year' => (int)$year,
                    'month' => (int)$month,
                    'total_orders' => rand(10, 50),
                    'total_sales' => rand(1000000, 5000000)
                ];
            }
        }
        
        return response()->json($data);
    }
}
