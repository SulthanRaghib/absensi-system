<?php

namespace App\Services\Geocoding;

use App\Contracts\GeocodingProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleGeocodingProvider implements GeocodingProviderInterface
{
    protected string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function reverseGeocode(float $lat, float $lng): ?string
    {
        try {
            $response = Http::timeout(5)->get('https://maps.googleapis.com/maps/api/geocode/json', [
                'latlng' => "{$lat},{$lng}",
                'key' => $this->apiKey,
                'language' => 'id',
            ]);

            if ($response->successful() && $response->json('status') === 'OK') {
                $results = $response->json('results');
                if (!empty($results)) {
                    return $results[0]['formatted_address'] ?? null;
                }
            }

            Log::warning("Google Geocoding failed or no results", [
                'lat' => $lat, 'lng' => $lng, 'response' => $response->json()
            ]);
        } catch (\Exception $e) {
            Log::error("Google Geocoding error: " . $e->getMessage());
        }

        return null;
    }
}
