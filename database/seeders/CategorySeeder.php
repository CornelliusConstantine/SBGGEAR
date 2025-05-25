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
                'name' => 'Helm Safety',
                'slug' => 'helm-safety',
                'description' => 'Berbagai jenis helm safety untuk perlindungan kepala',
                'icon' => 'helmet',
                'is_active' => true,
            ],
            [
                'name' => 'Sepatu Safety',
                'slug' => 'sepatu-safety',
                'description' => 'Sepatu safety untuk perlindungan kaki di tempat kerja',
                'icon' => 'boots',
                'is_active' => true,
            ],
            [
                'name' => 'Sarung Tangan',
                'slug' => 'sarung-tangan',
                'description' => 'Sarung tangan pelindung untuk berbagai jenis pekerjaan',
                'icon' => 'gloves',
                'is_active' => true,
            ],
            [
                'name' => 'Masker',
                'slug' => 'masker',
                'description' => 'Masker pelindung untuk keamanan pernapasan',
                'icon' => 'mask',
                'is_active' => true,
            ],
            [
                'name' => 'Rompi Safety',
                'slug' => 'rompi-safety',
                'description' => 'Rompi keselamatan dengan berbagai standar visibilitas',
                'icon' => 'vest',
                'is_active' => true,
            ],
            [
                'name' => 'Kacamata Safety',
                'slug' => 'kacamata-safety',
                'description' => 'Kacamata pelindung untuk keamanan mata',
                'icon' => 'glasses',
                'is_active' => true,
            ],
            [
                'name' => 'Peralatan P3K',
                'slug' => 'peralatan-p3k',
                'description' => 'Perlengkapan pertolongan pertama pada kecelakaan',
                'icon' => 'first-aid',
                'is_active' => true,
            ],
            [
                'name' => 'Pelindung Telinga',
                'slug' => 'pelindung-telinga',
                'description' => 'Peralatan pelindung pendengaran dari kebisingan',
                'icon' => 'ear-protection',
                'is_active' => true,
            ],
            [
                'name' => 'Peralatan Ketinggian',
                'slug' => 'peralatan-ketinggian',
                'description' => 'Peralatan safety untuk bekerja di ketinggian',
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