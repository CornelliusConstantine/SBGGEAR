<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            [
                'name' => 'Pelindung Mata',
                'slug' => 'eye-protection',
                'description' => 'Perlindungan mata dari partikel, cairan, dan cahaya berbahaya di tempat kerja',
                'icon' => 'glasses',
                'is_active' => true,
            ],
            [
                'name' => 'Alas Kaki',
                'slug' => 'footwear',
                'description' => 'Sepatu dan alas kaki khusus dengan fitur pelindung untuk keamanan kaki',
                'icon' => 'boots',
                'is_active' => true,
            ],
            [
                'name' => 'Pelindung Tangan',
                'slug' => 'hand-protection',
                'description' => 'Sarung tangan khusus untuk melindungi tangan dari berbagai risiko kerja',
                'icon' => 'gloves',
                'is_active' => true,
            ],
            [
                'name' => 'Pelindung Kepala',
                'slug' => 'head-protection',
                'description' => 'Helm dan perlengkapan pelindung kepala untuk keamanan di tempat kerja',
                'icon' => 'helmet',
                'is_active' => true,
            ],
            [
                'name' => 'Pelindung Pernapasan',
                'slug' => 'respiratory-protection',
                'description' => 'Masker dan respirator untuk melindungi sistem pernapasan dari debu, asap, dan partikel berbahaya',
                'icon' => 'mask',
                'is_active' => true,
            ],
            [
                'name' => 'Alat Visibilitas',
                'slug' => 'visibility',
                'description' => 'Peralatan dan pakaian dengan visibilitas tinggi untuk keamanan di area kerja',
                'icon' => 'vest',
                'is_active' => true,
            ],
            [
                'name' => 'Pelindung Telinga',
                'slug' => 'ear-protection',
                'description' => 'Peralatan untuk melindungi pendengaran dari kebisingan berlebih di lingkungan kerja',
                'icon' => 'ear-protection',
                'is_active' => true,
            ],
            [
                'name' => 'Alat Keselamatan Ketinggian',
                'slug' => 'fall-protection',
                'description' => 'Peralatan khusus untuk mencegah dan melindungi pekerja dari bahaya jatuh dari ketinggian',
                'icon' => 'height-safety',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
} 