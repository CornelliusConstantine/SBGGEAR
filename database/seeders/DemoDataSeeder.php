<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@sbggear.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '1234567890',
            'address' => '123 Admin Street',
            'city' => 'Admin City',
            'postal_code' => '12345',
        ]);

        // Create regular user
        User::create([
            'name' => 'Test User',
            'email' => 'user@sbggear.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'phone' => '0987654321',
            'address' => '456 User Avenue',
            'city' => 'User City',
            'postal_code' => '54321',
        ]);

        // Create categories
        $categories = [
            ['name' => 'Hard Hats', 'slug' => 'hard-hats'],
            ['name' => 'Safety Glasses', 'slug' => 'safety-glasses'],
            ['name' => 'Gloves', 'slug' => 'gloves'],
            ['name' => 'Safety Boots', 'slug' => 'safety-boots'],
            ['name' => 'Ear Protection', 'slug' => 'ear-protection'],
            ['name' => 'Respirators', 'slug' => 'respirators'],
            ['name' => 'High-Vis Clothing', 'slug' => 'high-vis-clothing'],
            ['name' => 'Fall Protection', 'slug' => 'fall-protection'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        // Create products
        $products = [
            [
                'name' => 'MSA V-Gard Hard Hat',
                'slug' => 'msa-v-gard-hard-hat',
                'description' => 'Industry standard hard hat with 4-point ratchet suspension.',
                'short_description' => 'Industry standard hard hat with 4-point ratchet suspension.',
                'price' => 250000,
                'stock' => 50,
                'category_id' => 1,
                'is_featured' => true,
                'is_active' => true,
                'brand' => 'MSA',
                'rating' => 4.5,
                'rating_count' => 28,
            ],
            [
                'name' => '3M SecureFit Safety Glasses',
                'slug' => '3m-securefit-safety-glasses',
                'description' => 'Lightweight safety glasses with anti-fog coating.',
                'short_description' => 'Lightweight safety glasses with anti-fog coating.',
                'price' => 120000,
                'stock' => 100,
                'category_id' => 2,
                'is_featured' => true,
                'is_active' => true,
                'brand' => '3M',
                'rating' => 4.2,
                'rating_count' => 45,
            ],
            [
                'name' => 'Mechanix Wear M-Pact Gloves',
                'slug' => 'mechanix-wear-m-pact-gloves',
                'description' => 'Heavy-duty work gloves with impact protection.',
                'short_description' => 'Heavy-duty work gloves with impact protection.',
                'price' => 350000,
                'stock' => 75,
                'category_id' => 3,
                'is_featured' => false,
                'is_active' => true,
                'brand' => 'Mechanix',
                'rating' => 4.7,
                'rating_count' => 63,
            ],
            [
                'name' => 'Timberland PRO Steel Toe Boots',
                'slug' => 'timberland-pro-steel-toe-boots',
                'description' => 'Waterproof leather boots with steel toe protection.',
                'short_description' => 'Waterproof leather boots with steel toe protection.',
                'price' => 1200000,
                'stock' => 30,
                'category_id' => 4,
                'is_featured' => true,
                'is_active' => true,
                'brand' => 'Timberland',
                'rating' => 4.8,
                'rating_count' => 92,
            ],
            [
                'name' => '3M Peltor Earmuffs',
                'slug' => '3m-peltor-earmuffs',
                'description' => 'High-performance hearing protection for noisy environments.',
                'short_description' => 'High-performance hearing protection for noisy environments.',
                'price' => 280000,
                'stock' => 60,
                'category_id' => 5,
                'is_featured' => false,
                'is_active' => true,
                'brand' => '3M',
                'rating' => 4.4,
                'rating_count' => 37,
            ],
            [
                'name' => 'Moldex N95 Respirator',
                'slug' => 'moldex-n95-respirator',
                'description' => 'NIOSH-approved N95 particulate respirator.',
                'short_description' => 'NIOSH-approved N95 particulate respirator.',
                'price' => 150000,
                'stock' => 200,
                'category_id' => 6,
                'is_featured' => false,
                'is_active' => true,
                'brand' => 'Moldex',
                'rating' => 4.3,
                'rating_count' => 56,
            ],
            [
                'name' => 'Reflective Safety Vest',
                'slug' => 'reflective-safety-vest',
                'description' => 'High-visibility vest with reflective strips.',
                'short_description' => 'High-visibility vest with reflective strips.',
                'price' => 85000,
                'stock' => 150,
                'category_id' => 7,
                'is_featured' => true,
                'is_active' => true,
                'brand' => 'SBG',
                'rating' => 4.0,
                'rating_count' => 29,
            ],
            [
                'name' => 'Full Body Harness',
                'slug' => 'full-body-harness',
                'description' => 'ANSI-compliant full body harness for fall protection.',
                'short_description' => 'ANSI-compliant full body harness for fall protection.',
                'price' => 950000,
                'stock' => 25,
                'category_id' => 8,
                'is_featured' => false,
                'is_active' => true,
                'brand' => 'Guardian',
                'rating' => 4.9,
                'rating_count' => 42,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
} 