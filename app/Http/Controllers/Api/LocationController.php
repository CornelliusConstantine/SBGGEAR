<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Get all provinces
     */
    public function provinces()
    {
        $provinces = [
            ['id' => 1, 'name' => 'DKI Jakarta'],
            ['id' => 2, 'name' => 'Jawa Barat'],
            ['id' => 3, 'name' => 'Jawa Tengah'],
            ['id' => 4, 'name' => 'Jawa Timur'],
            ['id' => 5, 'name' => 'Bali'],
            ['id' => 6, 'name' => 'Sumatera Utara'],
            ['id' => 7, 'name' => 'Sumatera Selatan'],
            ['id' => 8, 'name' => 'Kalimantan Timur'],
            ['id' => 9, 'name' => 'Sulawesi Selatan'],
            ['id' => 10, 'name' => 'Yogyakarta']
        ];
        
        return response()->json($provinces);
    }
    
    /**
     * Get cities by province ID
     */
    public function cities($provinceId)
    {
        $cities = $this->getCitiesByProvince($provinceId);
        
        if (empty($cities)) {
            return response()->json(['message' => 'Province not found'], 404);
        }
        
        return response()->json($cities);
    }
    
    /**
     * Get all cities
     */
    public function allCities()
    {
        $allCities = [];
        
        for ($i = 1; $i <= 10; $i++) {
            $cities = $this->getCitiesByProvince($i);
            $allCities = array_merge($allCities, $cities);
        }
        
        return response()->json($allCities);
    }
    
    /**
     * Helper method to get cities by province ID
     */
    private function getCitiesByProvince($provinceId)
    {
        $cities = [
            1 => [ // DKI Jakarta
                ['id' => 101, 'name' => 'Jakarta Pusat', 'province_id' => 1],
                ['id' => 102, 'name' => 'Jakarta Utara', 'province_id' => 1],
                ['id' => 103, 'name' => 'Jakarta Barat', 'province_id' => 1],
                ['id' => 104, 'name' => 'Jakarta Selatan', 'province_id' => 1],
                ['id' => 105, 'name' => 'Jakarta Timur', 'province_id' => 1]
            ],
            2 => [ // Jawa Barat
                ['id' => 201, 'name' => 'Bandung', 'province_id' => 2],
                ['id' => 202, 'name' => 'Bekasi', 'province_id' => 2],
                ['id' => 203, 'name' => 'Bogor', 'province_id' => 2],
                ['id' => 204, 'name' => 'Depok', 'province_id' => 2],
                ['id' => 205, 'name' => 'Cirebon', 'province_id' => 2]
            ],
            3 => [ // Jawa Tengah
                ['id' => 301, 'name' => 'Semarang', 'province_id' => 3],
                ['id' => 302, 'name' => 'Solo', 'province_id' => 3],
                ['id' => 303, 'name' => 'Magelang', 'province_id' => 3],
                ['id' => 304, 'name' => 'Pekalongan', 'province_id' => 3],
                ['id' => 305, 'name' => 'Tegal', 'province_id' => 3]
            ],
            4 => [ // Jawa Timur
                ['id' => 401, 'name' => 'Surabaya', 'province_id' => 4],
                ['id' => 402, 'name' => 'Malang', 'province_id' => 4],
                ['id' => 403, 'name' => 'Sidoarjo', 'province_id' => 4],
                ['id' => 404, 'name' => 'Kediri', 'province_id' => 4],
                ['id' => 405, 'name' => 'Jember', 'province_id' => 4]
            ],
            5 => [ // Bali
                ['id' => 501, 'name' => 'Denpasar', 'province_id' => 5],
                ['id' => 502, 'name' => 'Badung', 'province_id' => 5],
                ['id' => 503, 'name' => 'Gianyar', 'province_id' => 5],
                ['id' => 504, 'name' => 'Tabanan', 'province_id' => 5],
                ['id' => 505, 'name' => 'Buleleng', 'province_id' => 5]
            ],
            6 => [ // Sumatera Utara
                ['id' => 601, 'name' => 'Medan', 'province_id' => 6],
                ['id' => 602, 'name' => 'Binjai', 'province_id' => 6],
                ['id' => 603, 'name' => 'Deli Serdang', 'province_id' => 6],
                ['id' => 604, 'name' => 'Tebing Tinggi', 'province_id' => 6],
                ['id' => 605, 'name' => 'Pematang Siantar', 'province_id' => 6]
            ],
            7 => [ // Sumatera Selatan
                ['id' => 701, 'name' => 'Palembang', 'province_id' => 7],
                ['id' => 702, 'name' => 'Prabumulih', 'province_id' => 7],
                ['id' => 703, 'name' => 'Lubuklinggau', 'province_id' => 7],
                ['id' => 704, 'name' => 'Pagar Alam', 'province_id' => 7],
                ['id' => 705, 'name' => 'Ogan Komering Ilir', 'province_id' => 7]
            ],
            8 => [ // Kalimantan Timur
                ['id' => 801, 'name' => 'Balikpapan', 'province_id' => 8],
                ['id' => 802, 'name' => 'Samarinda', 'province_id' => 8],
                ['id' => 803, 'name' => 'Bontang', 'province_id' => 8],
                ['id' => 804, 'name' => 'Kutai Kartanegara', 'province_id' => 8],
                ['id' => 805, 'name' => 'Berau', 'province_id' => 8]
            ],
            9 => [ // Sulawesi Selatan
                ['id' => 901, 'name' => 'Makassar', 'province_id' => 9],
                ['id' => 902, 'name' => 'Palopo', 'province_id' => 9],
                ['id' => 903, 'name' => 'Parepare', 'province_id' => 9],
                ['id' => 904, 'name' => 'Gowa', 'province_id' => 9],
                ['id' => 905, 'name' => 'Maros', 'province_id' => 9]
            ],
            10 => [ // Yogyakarta
                ['id' => 1001, 'name' => 'Yogyakarta', 'province_id' => 10],
                ['id' => 1002, 'name' => 'Sleman', 'province_id' => 10],
                ['id' => 1003, 'name' => 'Bantul', 'province_id' => 10],
                ['id' => 1004, 'name' => 'Kulon Progo', 'province_id' => 10],
                ['id' => 1005, 'name' => 'Gunungkidul', 'province_id' => 10]
            ]
        ];
        
        return $cities[$provinceId] ?? [];
    }
} 