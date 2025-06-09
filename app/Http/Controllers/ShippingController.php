<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

/**
 * DEPRECATED: This controller is no longer in use.
 * Please use App\Http\Controllers\Api\ShippingController instead.
 */
class ShippingController extends Controller
{
    protected $apiKey;
    protected $baseUrl;
    
    public function __construct()
    {
        // RajaOngkir API configuration
        $this->apiKey = env('RAJAONGKIR_API_KEY', 'your_api_key_here');
        $this->baseUrl = 'https://api.rajaongkir.com/starter';
    }
    
    /**
     * Get all provinces
     */
    public function getProvinces()
    {
        // Cache provinces for 1 day to reduce API calls
        $provinces = Cache::remember('provinces', 86400, function () {
            $response = Http::withHeaders([
                'key' => $this->apiKey
            ])->get($this->baseUrl . '/province');
            
            if ($response->successful()) {
                return $response->json()['rajaongkir']['results'];
            }
            
            return [];
        });
        
        return response()->json([
            'status' => 'success',
            'results' => $provinces
        ]);
    }
    
    /**
     * Get cities by province
     */
    public function getCities(Request $request)
    {
        $provinceId = $request->query('province');
        
        if (!$provinceId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Province ID is required'
            ], 400);
        }
        
        // Cache cities by province for 1 day
        $cacheKey = 'cities_' . $provinceId;
        $cities = Cache::remember($cacheKey, 86400, function () use ($provinceId) {
            $response = Http::withHeaders([
                'key' => $this->apiKey
            ])->get($this->baseUrl . '/city', [
                'province' => $provinceId
            ]);
            
            if ($response->successful()) {
                return $response->json()['rajaongkir']['results'];
            }
            
            return [];
        });
        
        return response()->json([
            'status' => 'success',
            'results' => $cities
        ]);
    }
    
    /**
     * Calculate shipping cost
     */
    public function calculateCost(Request $request)
    {
        $request->validate([
            'origin' => 'required|numeric',
            'destination' => 'required|numeric',
            'weight' => 'required|numeric|min:1000',
            'courier' => 'required|string'
        ]);
        
        $response = Http::withHeaders([
            'key' => $this->apiKey
        ])->post($this->baseUrl . '/cost', [
            'origin' => $request->origin,
            'destination' => $request->destination,
            'weight' => $request->weight,
            'courier' => $request->courier
        ]);
        
        if ($response->successful()) {
            return response()->json([
                'status' => 'success',
                'results' => $response->json()['rajaongkir']['results']
            ]);
        }
        
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to calculate shipping cost'
        ], 500);
    }
} 