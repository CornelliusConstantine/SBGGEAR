<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user if it doesn't exist
        if (!User::where('email', 'admin@sbggear.com')->exists()) {
            User::create([
                'name' => 'Admin User',
                'email' => 'admin@sbggear.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role' => 'admin',
                'remember_token' => Str::random(10),
            ]);
        }

        // Create categories
        $categories = [
            ['name' => 'Head Protection', 'slug' => 'head-protection'],
            ['name' => 'Eye Protection', 'slug' => 'eye-protection'],
            ['name' => 'Respiratory Protection', 'slug' => 'respiratory-protection'],
            ['name' => 'Hand Protection', 'slug' => 'hand-protection'],
            ['name' => 'Visibility', 'slug' => 'visibility'],
            ['name' => 'Footwear', 'slug' => 'footwear'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                ['name' => $category['name']]
            );
        }

        // Create 20 regular users
        $this->createUsers(20);

        // Create products for each category
        $this->createProducts();

        // Create orders with varying statuses
        $this->createOrders();
    }

    /**
     * Create sample users
     */
    private function createUsers($count)
    {
        for ($i = 1; $i <= $count; $i++) {
            User::updateOrCreate(
                ['email' => "user{$i}@example.com"],
                [
                    'name' => "User {$i}",
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                    'role' => 'customer',
                    'remember_token' => Str::random(10),
                    'phone' => '08' . rand(10000000, 99999999),
                    'address' => "Address for User {$i}",
                    'city' => $this->getRandomCity(),
                    'province' => $this->getRandomProvince(),
                    'postal_code' => rand(10000, 99999),
                ]
            );
        }
    }

    /**
     * Create sample products
     */
    private function createProducts()
    {
        $categories = Category::all();

        foreach ($categories as $category) {
            for ($i = 1; $i <= 5; $i++) {
                $name = $this->getProductNameByCategory($category->name, $i);
                $price = rand(50000, 2000000);
                $stock = rand(0, 50);
                
                Product::updateOrCreate(
                    ['slug' => Str::slug($name)],
                    [
                        'category_id' => $category->id,
                        'name' => $name,
                        'description' => "Description for {$name}. High-quality safety product.",
                        'price' => $price,
                        'stock' => $stock,
                        'sku' => strtoupper(substr($category->slug, 0, 3)) . '-' . rand(1000, 9999),
                        'specifications' => ['material' => 'High-quality', 'standard' => 'ISO Certified'],
                        'images' => ['main' => 'default.jpg', 'gallery' => []],
                        'is_active' => true,
                        'weight' => rand(100, 5000) / 100,
                        'is_featured' => $i == 1, // First product of each category is featured
                    ]
                );
            }
        }
    }

    /**
     * Create sample orders
     */
    private function createOrders()
    {
        $users = User::where('role', 'customer')->get();
        $products = Product::all();
        $statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        $paymentStatuses = ['paid', 'unpaid', 'refunded'];
        
        // Create orders for the past 3 months
        for ($day = 90; $day >= 0; $day--) {
            $orderDate = Carbon::now()->subDays($day);
            
            // Increase orders for recent days
            $orderCount = $day < 30 ? rand(1, 3) : rand(0, 2);
            
            for ($i = 0; $i < $orderCount; $i++) {
                $user = $users->random();
                $status = $statuses[array_rand($statuses)];
                $paymentStatus = $paymentStatuses[array_rand($paymentStatuses)];
                
                // Calculate order items and subtotal first
                $orderProducts = $products->random(rand(1, 5));
                $subtotal = 0;
                $orderItems = [];
                
                foreach ($orderProducts as $product) {
                    $quantity = rand(1, 3);
                    $price = $product->price;
                    $itemSubtotal = $price * $quantity;
                    $subtotal += $itemSubtotal;
                    
                    $orderItems[] = [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'price' => $price,
                        'quantity' => $quantity,
                        'subtotal' => $itemSubtotal,
                    ];
                }
                
                // Set shipping cost
                $shippingCost = rand(10000, 50000);
                
                // Create order with subtotal already set
                $microTime = str_replace('.', '', microtime(true));
                $order = Order::create([
                    'order_number' => 'ORD-' . $orderDate->format('Ymd') . '-' . sprintf('%03d', rand(1, 999)) . substr($microTime, -4),
                    'user_id' => $user->id,
                    'status' => $status,
                    'payment_status' => $paymentStatus,
                    'payment_method' => 'transfer',
                    'shipping_name' => $user->name,
                    'shipping_phone' => $user->phone,
                    'shipping_address' => $user->address,
                    'shipping_city' => $user->city,
                    'shipping_province' => $user->province,
                    'shipping_postal_code' => $user->postal_code,
                    'shipping_method' => 'JNE Regular',
                    'shipping_cost' => $shippingCost,
                    'subtotal' => $subtotal,
                    'total_amount' => $subtotal + $shippingCost,
                    'notes' => rand(0, 1) ? 'Please handle with care.' : null,
                    'created_at' => $orderDate,
                    'updated_at' => $orderDate,
                ]);
                
                // Add order items
                foreach ($orderItems as $item) {
                    $item['order_id'] = $order->id;
                    OrderItem::create($item);
                }
                
                // Set appropriate timestamps based on status
                if ($status != 'pending') {
                    if ($paymentStatus == 'paid') {
                        $order->paid_at = $orderDate->copy()->addHours(rand(1, 24));
                    }
                    
                    if ($status == 'shipped' || $status == 'delivered') {
                        $order->shipped_at = $orderDate->copy()->addDays(rand(1, 3));
                        $order->tracking_number = 'JNE' . rand(1000000, 9999999);
                    }
                    
                    if ($status == 'delivered') {
                        $order->delivered_at = $orderDate->copy()->addDays(rand(4, 7));
                    }
                    
                    if ($status == 'cancelled') {
                        $order->cancelled_at = $orderDate->copy()->addHours(rand(1, 48));
                    }
                }
                
                $order->save();
            }
        }
    }

    /**
     * Get random city
     */
    private function getRandomCity()
    {
        $cities = ['Jakarta', 'Surabaya', 'Bandung', 'Medan', 'Semarang', 'Makassar', 'Yogyakarta', 'Palembang', 'Balikpapan'];
        return $cities[array_rand($cities)];
    }

    /**
     * Get random province
     */
    private function getRandomProvince()
    {
        $provinces = ['DKI Jakarta', 'Jawa Barat', 'Jawa Timur', 'Jawa Tengah', 'Sumatera Utara', 'Sulawesi Selatan', 'Kalimantan Timur', 'DI Yogyakarta', 'Sumatera Selatan'];
        return $provinces[array_rand($provinces)];
    }

    /**
     * Get product name by category
     */
    private function getProductNameByCategory($category, $index)
    {
        $products = [
            'Head Protection' => [
                'Safety Helmet Type A',
                'Construction Hard Hat',
                'Tactical Helmet',
                'Mining Safety Helmet',
                'Bump Cap Pro'
            ],
            'Eye Protection' => [
                'Safety Goggles',
                'Anti-Fog Glasses',
                'Welding Face Shield',
                'Chemical Splash Goggles',
                'Impact Resistant Eyewear'
            ],
            'Respiratory Protection' => [
                'N95 Respirator',
                'Half Face Mask',
                'Full Face Respirator',
                'Dust Mask Premium',
                'Chemical Filter Mask'
            ],
            'Hand Protection' => [
                'Cut Resistant Gloves',
                'Chemical Resistant Gloves',
                'Heat Resistant Gloves',
                'Anti-Vibration Gloves',
                'Electrical Insulating Gloves'
            ],
            'Visibility' => [
                'Reflective Vest XL',
                'High Visibility Jacket',
                'LED Safety Vest',
                'Reflective Arm Bands',
                'Warning Tape 100m'
            ],
            'Footwear' => [
                'Steel Toe Boots',
                'Chemical Resistant Shoes',
                'Anti-Slip Work Boots',
                'Electrical Hazard Footwear',
                'Waterproof Safety Shoes'
            ]
        ];
        
        return $products[$category][$index - 1] ?? "{$category} Product {$index}";
    }
}
