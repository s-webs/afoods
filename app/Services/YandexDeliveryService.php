<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YandexDeliveryService
{
    private string $apiUrl = 'https://b2b.taxi.yandex.net';
    private ?string $apiToken;

    public function __construct()
    {
        $this->apiToken = env('YANDEX_DELIVERY_API_TOKEN');
    }

    /**
     * Calculate delivery options for given coordinates.
     */
    public function calculateDelivery(array $fromCoords, array $toCoords, array $items = []): array
    {
        if (!$this->apiToken) {
            return [];
        }

        try {
            // Try check-price endpoint first (for Kazakhstan and other non-Russia regions)
            $checkPriceResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . '/b2b/cargo/integration/v2/check-price', [
                'route_points' => [
                    [
                        'coordinates' => [$fromCoords[1], $fromCoords[0]], // [longitude, latitude]
                    ],
                    [
                        'coordinates' => [$toCoords[1], $toCoords[0]], // [longitude, latitude]
                    ],
                ],
            ]);

            if ($checkPriceResponse->successful()) {
                $data = $checkPriceResponse->json();
                Log::info('Yandex Delivery API check-price success', [
                    'response' => $data,
                ]);
                return $data;
            }

            // Fallback to offers/calculate endpoint (for Russia)
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . '/b2b/cargo/integration/v2/offers/calculate', [
                'requirements' => [
                    'cargo_type' => 1, // Cargo type
                    'cargo_loaders' => 1,
                ],
                'route_points' => [
                    [
                        'coordinates' => [$fromCoords[1], $fromCoords[0]], // [longitude, latitude]
                    ],
                    [
                        'coordinates' => [$toCoords[1], $toCoords[0]], // [longitude, latitude]
                    ],
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Yandex Delivery API offers/calculate success', [
                    'response' => $data,
                ]);
                return $data;
            }

            Log::error('Yandex Delivery API error', [
                'check_price_status' => $checkPriceResponse->status(),
                'check_price_body' => $checkPriceResponse->body(),
                'offers_status' => $response->status(),
                'offers_body' => $response->body(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('Yandex Delivery Service error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [];
        }
    }

    /**
     * Calculate distance between two coordinates using Haversine formula.
     */
    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // Earth radius in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        return $distance;
    }

    /**
     * Calculate delivery cost based on distance (fallback method).
     */
    private function calculateDeliveryByDistance(float $distance): array
    {
        // Base price in KZT
        $basePrice = 300; // Минимальная стоимость доставки
        $pricePerKm = 30; // Стоимость за километр
        
        // Calculate price
        $price = $basePrice + ($distance * $pricePerKm);
        
        // Round to nearest 50
        $price = round($price / 50) * 50;
        
        // Minimum price
        if ($price < $basePrice) {
            $price = $basePrice;
        }
        
        // Estimate time (average speed 25 km/h in city with traffic)
        $estimatedTime = ($distance / 25) * 3600; // in seconds
        
        return [
            'id' => 'distance_based',
            'name' => 'Доставка курьером',
            'price' => (int)$price,
            'currency' => 'KZT',
            'estimated_time' => (int)$estimatedTime,
            'estimated_distance' => round($distance * 1000), // in meters
        ];
    }

    /**
     * Get delivery options formatted for frontend.
     */
    public function getDeliveryOptions(array $fromCoords, array $toCoords): array
    {
        $result = $this->calculateDelivery($fromCoords, $toCoords);
        $options = [];

        // Check different possible response structures
        // For check-price endpoint response
        if (isset($result['price']) || isset($result['currency'])) {
            $options[] = [
                'id' => $result['offer_id'] ?? null,
                'name' => $result['name'] ?? 'Доставка Яндекс Доставкой',
                'price' => $result['price'] ?? 0,
                'currency' => $result['currency'] ?? 'KZT',
                'estimated_time' => $result['estimated_time'] ?? $result['estimated_duration'] ?? null,
                'estimated_distance' => $result['estimated_distance'] ?? $result['distance'] ?? null,
            ];
        } elseif (isset($result['offers']) && is_array($result['offers']) && !empty($result['offers'])) {
            // For offers/calculate endpoint response
            foreach ($result['offers'] as $offer) {
                $options[] = [
                    'id' => $offer['offer_id'] ?? $offer['id'] ?? null,
                    'name' => $offer['name'] ?? $offer['tariff_name'] ?? 'Доставка',
                    'price' => $offer['price'] ?? $offer['cost'] ?? 0,
                    'currency' => $offer['currency'] ?? 'KZT',
                    'estimated_time' => $offer['estimated_time'] ?? $offer['estimated_duration'] ?? null,
                    'estimated_distance' => $offer['estimated_distance'] ?? $offer['distance'] ?? null,
                ];
            }
        } elseif (isset($result['options']) && is_array($result['options']) && !empty($result['options'])) {
            // Alternative response structure
            foreach ($result['options'] as $option) {
                $options[] = [
                    'id' => $option['id'] ?? null,
                    'name' => $option['name'] ?? 'Доставка',
                    'price' => $option['price'] ?? $option['cost'] ?? 0,
                    'currency' => $option['currency'] ?? 'KZT',
                    'estimated_time' => $option['estimated_time'] ?? $option['duration'] ?? null,
                    'estimated_distance' => $option['estimated_distance'] ?? $option['distance'] ?? null,
                ];
            }
        }

        // If no options from API, calculate based on distance (fallback)
        if (empty($options)) {
            Log::info('Using distance-based delivery calculation as fallback', [
                'from' => $fromCoords,
                'to' => $toCoords,
            ]);
            
            $distance = $this->calculateDistance(
                $fromCoords[0],
                $fromCoords[1],
                $toCoords[0],
                $toCoords[1]
            );
            
            $options[] = $this->calculateDeliveryByDistance($distance);
        }

        return $options;
    }
}
