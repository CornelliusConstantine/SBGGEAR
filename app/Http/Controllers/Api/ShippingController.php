<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ShippingController extends Controller
{
    /**
     * Get all provinces
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProvinces()
    {
        // Cache provinces for 1 week to reduce API calls
        $provinces = Cache::remember('provinces', 604800, function () {
            // For demo purposes, we'll return a static list of provinces
            return [
                ['province_id' => '1', 'province' => 'Bali'],
                ['province_id' => '2', 'province' => 'Bangka Belitung'],
                ['province_id' => '3', 'province' => 'Banten'],
                ['province_id' => '4', 'province' => 'Bengkulu'],
                ['province_id' => '5', 'province' => 'DI Yogyakarta'],
                ['province_id' => '6', 'province' => 'DKI Jakarta'],
                ['province_id' => '7', 'province' => 'Gorontalo'],
                ['province_id' => '8', 'province' => 'Jambi'],
                ['province_id' => '9', 'province' => 'Jawa Barat'],
                ['province_id' => '10', 'province' => 'Jawa Tengah'],
                ['province_id' => '11', 'province' => 'Jawa Timur'],
                ['province_id' => '12', 'province' => 'Kalimantan Barat'],
                ['province_id' => '13', 'province' => 'Kalimantan Selatan'],
                ['province_id' => '14', 'province' => 'Kalimantan Tengah'],
                ['province_id' => '15', 'province' => 'Kalimantan Timur'],
                ['province_id' => '16', 'province' => 'Kalimantan Utara'],
                ['province_id' => '17', 'province' => 'Kepulauan Riau'],
                ['province_id' => '18', 'province' => 'Lampung'],
                ['province_id' => '19', 'province' => 'Maluku'],
                ['province_id' => '20', 'province' => 'Maluku Utara'],
                ['province_id' => '21', 'province' => 'Nanggroe Aceh Darussalam (NAD)'],
                ['province_id' => '22', 'province' => 'Nusa Tenggara Barat (NTB)'],
                ['province_id' => '23', 'province' => 'Nusa Tenggara Timur (NTT)'],
                ['province_id' => '24', 'province' => 'Papua'],
                ['province_id' => '25', 'province' => 'Papua Barat'],
                ['province_id' => '26', 'province' => 'Riau'],
                ['province_id' => '27', 'province' => 'Sulawesi Barat'],
                ['province_id' => '28', 'province' => 'Sulawesi Selatan'],
                ['province_id' => '29', 'province' => 'Sulawesi Tengah'],
                ['province_id' => '30', 'province' => 'Sulawesi Tenggara'],
                ['province_id' => '31', 'province' => 'Sulawesi Utara'],
                ['province_id' => '32', 'province' => 'Sumatera Barat'],
                ['province_id' => '33', 'province' => 'Sumatera Selatan'],
                ['province_id' => '34', 'province' => 'Sumatera Utara'],
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $provinces
        ]);
    }

    /**
     * Get cities by province ID
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCities(Request $request)
    {
        $request->validate([
            'province_id' => 'required|string',
        ]);

        $provinceId = $request->province_id;

        // Cache cities for each province for 1 week
        $cities = Cache::remember('cities_' . $provinceId, 604800, function () use ($provinceId) {
            // For demo purposes, we'll return a static list of cities based on province
            $allCities = [
                // Bali
                '1' => [
                    ['city_id' => '17', 'type' => 'Kabupaten', 'city_name' => 'Badung'],
                    ['city_id' => '32', 'type' => 'Kabupaten', 'city_name' => 'Bangli'],
                    ['city_id' => '94', 'type' => 'Kabupaten', 'city_name' => 'Buleleng'],
                    ['city_id' => '114', 'type' => 'Kabupaten', 'city_name' => 'Gianyar'],
                    ['city_id' => '128', 'type' => 'Kabupaten', 'city_name' => 'Jembrana'],
                    ['city_id' => '161', 'type' => 'Kabupaten', 'city_name' => 'Karangasem'],
                    ['city_id' => '170', 'type' => 'Kabupaten', 'city_name' => 'Klungkung'],
                    ['city_id' => '291', 'type' => 'Kabupaten', 'city_name' => 'Tabanan'],
                    ['city_id' => '108', 'type' => 'Kota', 'city_name' => 'Denpasar']
                ],
                // Bangka Belitung
                '2' => [
                    ['city_id' => '33', 'type' => 'Kabupaten', 'city_name' => 'Bangka'],
                    ['city_id' => '34', 'type' => 'Kabupaten', 'city_name' => 'Bangka Barat'],
                    ['city_id' => '35', 'type' => 'Kabupaten', 'city_name' => 'Bangka Selatan'],
                    ['city_id' => '36', 'type' => 'Kabupaten', 'city_name' => 'Bangka Tengah'],
                    ['city_id' => '37', 'type' => 'Kabupaten', 'city_name' => 'Belitung'],
                    ['city_id' => '38', 'type' => 'Kabupaten', 'city_name' => 'Belitung Timur'],
                    ['city_id' => '280', 'type' => 'Kota', 'city_name' => 'Pangkal Pinang']
                ],
                // Add more provinces and cities as needed
                '3' => [
                    ['city_id' => '106', 'type' => 'Kabupaten', 'city_name' => 'Lebak'],
                    ['city_id' => '207', 'type' => 'Kabupaten', 'city_name' => 'Pandeglang'],
                    ['city_id' => '249', 'type' => 'Kabupaten', 'city_name' => 'Serang'],
                    ['city_id' => '302', 'type' => 'Kabupaten', 'city_name' => 'Tangerang'],
                    ['city_id' => '74', 'type' => 'Kota', 'city_name' => 'Cilegon'],
                    ['city_id' => '250', 'type' => 'Kota', 'city_name' => 'Serang'],
                    ['city_id' => '301', 'type' => 'Kota', 'city_name' => 'Tangerang'],
                    ['city_id' => '303', 'type' => 'Kota', 'city_name' => 'Tangerang Selatan']
                ],
                // DKI Jakarta
                '6' => [
                    ['city_id' => '151', 'type' => 'Kota', 'city_name' => 'Jakarta Barat'],
                    ['city_id' => '152', 'type' => 'Kota', 'city_name' => 'Jakarta Pusat'],
                    ['city_id' => '153', 'type' => 'Kota', 'city_name' => 'Jakarta Selatan'],
                    ['city_id' => '154', 'type' => 'Kota', 'city_name' => 'Jakarta Timur'],
                    ['city_id' => '155', 'type' => 'Kota', 'city_name' => 'Jakarta Utara'],
                    ['city_id' => '189', 'type' => 'Kabupaten', 'city_name' => 'Kepulauan Seribu']
                ],
                // Default for other provinces
                'default' => [
                    ['city_id' => '1', 'type' => 'Kota', 'city_name' => 'Default City']
                ]
            ];

            return $allCities[$provinceId] ?? $allCities['default'];
        });

        return response()->json([
            'status' => true,
            'data' => $cities
        ]);
    }

    /**
     * Calculate shipping cost
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function calculateShipping(Request $request)
    {
        $request->validate([
            'city_id' => 'required|string',
            'weight' => 'required|integer|min:1',
            'courier' => 'required|string|in:jne,pos,tiki'
        ]);

        $cityId = $request->city_id;
        $weight = $request->weight;
        $courier = $request->courier;

        // For demo purposes, we'll calculate shipping costs based on weight and courier
        $shippingCosts = [];

        switch ($courier) {
            case 'jne':
                $shippingCosts = [
                    [
                        'service' => 'OKE',
                        'description' => 'Ongkos Kirim Ekonomis',
                        'cost' => [
                            [
                                'value' => ceil($weight / 250) * 8000,
                                'etd' => '3-6',
                                'note' => ''
                            ]
                        ]
                    ],
                    [
                        'service' => 'REG',
                        'description' => 'Layanan Reguler',
                        'cost' => [
                            [
                                'value' => ceil($weight / 250) * 12000,
                                'etd' => '2-3',
                                'note' => ''
                            ]
                        ]
                    ],
                    [
                        'service' => 'YES',
                        'description' => 'Yakin Esok Sampai',
                        'cost' => [
                            [
                                'value' => ceil($weight / 250) * 18000,
                                'etd' => '1',
                                'note' => ''
                            ]
                        ]
                    ]
                ];
                break;
            
            case 'pos':
                $shippingCosts = [
                    [
                        'service' => 'Pos Reguler',
                        'description' => 'Pos Reguler',
                        'cost' => [
                            [
                                'value' => ceil($weight / 250) * 7000,
                                'etd' => '3-5',
                                'note' => ''
                            ]
                        ]
                    ],
                    [
                        'service' => 'Pos Express',
                        'description' => 'Pos Express',
                        'cost' => [
                            [
                                'value' => ceil($weight / 250) * 15000,
                                'etd' => '1-2',
                                'note' => ''
                            ]
                        ]
                    ]
                ];
                break;
            
            case 'tiki':
                $shippingCosts = [
                    [
                        'service' => 'ECO',
                        'description' => 'Economy Service',
                        'cost' => [
                            [
                                'value' => ceil($weight / 250) * 9000,
                                'etd' => '3-4',
                                'note' => ''
                            ]
                        ]
                    ],
                    [
                        'service' => 'REG',
                        'description' => 'Regular Service',
                        'cost' => [
                            [
                                'value' => ceil($weight / 250) * 13000,
                                'etd' => '2',
                                'note' => ''
                            ]
                        ]
                    ],
                    [
                        'service' => 'ONS',
                        'description' => 'Over Night Service',
                        'cost' => [
                            [
                                'value' => ceil($weight / 250) * 20000,
                                'etd' => '1',
                                'note' => ''
                            ]
                        ]
                    ]
                ];
                break;
        }

        return response()->json([
            'status' => true,
            'data' => [
                'origin' => '152', // Jakarta Pusat as origin
                'destination' => $cityId,
                'weight' => $weight,
                'courier' => $courier,
                'results' => $shippingCosts
            ]
        ]);
    }

    /**
     * Save shipping details to session
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveShippingDetails(Request $request)
    {
        $request->validate([
            'shipping_name' => 'required|string|max:255',
            'shipping_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string',
            'shipping_city' => 'required|string',
            'shipping_province' => 'required|string',
            'shipping_postal_code' => 'required|string|max:10',
            'shipping_cost' => 'required|numeric|min:0',
            'shipping_service' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        // Save shipping details to session
        $shippingDetails = [
            'shipping_name' => $request->shipping_name,
            'shipping_phone' => $request->shipping_phone,
            'shipping_address' => $request->shipping_address,
            'shipping_city' => $request->shipping_city,
            'shipping_province' => $request->shipping_province,
            'shipping_postal_code' => $request->shipping_postal_code,
            'shipping_cost' => $request->shipping_cost,
            'shipping_service' => $request->shipping_service,
            'notes' => $request->notes,
        ];

        session(['shipping_details' => $shippingDetails]);

        return response()->json([
            'status' => true,
            'message' => 'Shipping details saved successfully',
            'data' => $shippingDetails
        ]);
    }
} 