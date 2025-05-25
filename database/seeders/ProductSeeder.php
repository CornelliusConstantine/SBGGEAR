<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create products
        $products = [
            [
                'name' => 'MSA V-Gard Hard Hat',
                'slug' => 'msa-v-gard-hard-hat',
                'description' => 'Industry standard hard hat with 4-point ratchet suspension.',
                'price' => 250000,
                'stock' => 50,
                'category_id' => 1,
                'sku' => 'MSA-001',
                'is_active' => true,
                'weight' => 500.00,
                'specifications' => json_encode([
                    'Material' => 'High-density polyethylene',
                    'Color' => 'White',
                    'Certification' => 'ANSI Z89.1-2014 Type I, Class E'
                ]),
                'images' => json_encode([
                    'main' => 'msa-v-gard-main.jpg',
                    'thumbnails' => [
                        'msa-v-gard-1.jpg',
                        'msa-v-gard-2.jpg'
                    ]
                ])
            ],
            [
                'name' => '3M SecureFit Safety Glasses',
                'slug' => '3m-securefit-safety-glasses',
                'description' => 'Lightweight safety glasses with anti-fog coating.',
                'price' => 120000,
                'stock' => 100,
                'category_id' => 2,
                'sku' => '3M-SG001',
                'is_active' => true,
                'weight' => 150.00,
                'specifications' => json_encode([
                    'Material' => 'Polycarbonate',
                    'Color' => 'Clear',
                    'Certification' => 'ANSI Z87.1-2020'
                ]),
                'images' => json_encode([
                    'main' => '3m-securefit-main.jpg',
                    'thumbnails' => [
                        '3m-securefit-1.jpg',
                        '3m-securefit-2.jpg'
                    ]
                ])
            ],
            [
                'name' => 'Mechanix Wear M-Pact Gloves',
                'slug' => 'mechanix-wear-m-pact-gloves',
                'description' => 'Heavy-duty work gloves with impact protection.',
                'price' => 350000,
                'stock' => 75,
                'category_id' => 3,
                'sku' => 'MECH-001',
                'is_active' => true,
                'weight' => 250.00,
                'specifications' => json_encode([
                    'Material' => 'Synthetic leather, TPR',
                    'Size' => 'L',
                    'Color' => 'Black/Yellow'
                ]),
                'images' => json_encode([
                    'main' => 'mechanix-mpact-main.jpg',
                    'thumbnails' => [
                        'mechanix-mpact-1.jpg',
                        'mechanix-mpact-2.jpg'
                    ]
                ])
            ],
            [
                'name' => 'Timberland PRO Steel Toe Boots',
                'slug' => 'timberland-pro-steel-toe-boots',
                'description' => 'Waterproof leather boots with steel toe protection.',
                'price' => 1200000,
                'stock' => 30,
                'category_id' => 4,
                'sku' => 'TIMB-001',
                'is_active' => true,
                'weight' => 1200.00,
                'specifications' => json_encode([
                    'Material' => 'Full-grain leather',
                    'Size' => '42',
                    'Color' => 'Wheat',
                    'Certification' => 'ASTM F2413-18'
                ]),
                'images' => json_encode([
                    'main' => 'timberland-pro-main.jpg',
                    'thumbnails' => [
                        'timberland-pro-1.jpg',
                        'timberland-pro-2.jpg'
                    ]
                ])
            ],
            [
                'name' => '3M Peltor Earmuffs',
                'slug' => '3m-peltor-earmuffs',
                'description' => 'High-performance hearing protection for noisy environments.',
                'price' => 280000,
                'stock' => 60,
                'category_id' => 5,
                'sku' => '3M-EM001',
                'is_active' => true,
                'weight' => 350.00,
                'specifications' => json_encode([
                    'NRR Rating' => '28 dB',
                    'Color' => 'Black/Red',
                    'Certification' => 'ANSI S3.19-1974'
                ]),
                'images' => json_encode([
                    'main' => '3m-peltor-main.jpg',
                    'thumbnails' => [
                        '3m-peltor-1.jpg',
                        '3m-peltor-2.jpg'
                    ]
                ])
            ],
            [
                'name' => 'Moldex N95 Respirator',
                'slug' => 'moldex-n95-respirator',
                'description' => 'NIOSH-approved N95 particulate respirator.',
                'price' => 150000,
                'stock' => 200,
                'category_id' => 6,
                'sku' => 'MOLD-001',
                'is_active' => true,
                'weight' => 50.00,
                'specifications' => json_encode([
                    'Type' => 'N95',
                    'Certification' => 'NIOSH-approved',
                    'Valve' => 'Yes'
                ]),
                'images' => json_encode([
                    'main' => 'moldex-n95-main.jpg',
                    'thumbnails' => [
                        'moldex-n95-1.jpg',
                        'moldex-n95-2.jpg'
                    ]
                ])
            ],
            [
                'name' => 'Reflective Safety Vest',
                'slug' => 'reflective-safety-vest',
                'description' => 'High-visibility vest with reflective strips.',
                'price' => 85000,
                'stock' => 150,
                'category_id' => 7,
                'sku' => 'SBG-001',
                'is_active' => true,
                'weight' => 200.00,
                'specifications' => json_encode([
                    'Material' => 'Polyester mesh',
                    'Size' => 'XL',
                    'Color' => 'Neon Yellow',
                    'Certification' => 'ANSI/ISEA 107-2020 Type R, Class 2'
                ]),
                'images' => json_encode([
                    'main' => 'safety-vest-main.jpg',
                    'thumbnails' => [
                        'safety-vest-1.jpg',
                        'safety-vest-2.jpg'
                    ]
                ])
            ],
            [
                'name' => 'Full Body Harness',
                'slug' => 'full-body-harness',
                'description' => 'ANSI-compliant full body harness for fall protection.',
                'price' => 950000,
                'stock' => 25,
                'category_id' => 8,
                'sku' => 'GUARD-001',
                'is_active' => true,
                'weight' => 1500.00,
                'specifications' => json_encode([
                    'Material' => 'Polyester webbing',
                    'Size' => 'Universal',
                    'D-Rings' => '3',
                    'Certification' => 'ANSI Z359.11-2014'
                ]),
                'images' => json_encode([
                    'main' => 'body-harness-main.jpg',
                    'thumbnails' => [
                        'body-harness-1.jpg',
                        'body-harness-2.jpg'
                    ]
                ])
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['slug' => $product['slug']],
                $product
            );
        }
    }
} 