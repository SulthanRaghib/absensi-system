<?php

namespace App\Services\Geocoding;

use App\Contracts\GeocodingProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NominatimGeocodingProvider implements GeocodingProviderInterface
{
    public function reverseGeocode(float $lat, float $lng): ?string
    {
        try {
            // Nominatim requires a User-Agent, please configure it properly
            $appName = config('app.name', 'Laravel');
            $response = Http::withHeaders([
                'User-Agent' => $appName . ' / Reverse Geocoding Feature',
            ])->timeout(5)->get('https://nominatim.openstreetmap.org/reverse', [
                'lat' => $lat,
                'lon' => $lng,
                'format' => 'json',
                'accept-language' => 'id',
            ]);

            if ($response->successful() && $response->json('display_name')) {
                return $response->json('display_name');
            }

            Log::warning("Nominatim Geocoding failed or no results", [
                'lat' => $lat, 'lng' => $lng, 'response' => $response->json()
            ]);
        } catch (\Exception $e) {
            Log::error("Nominatim Geocoding error: " . $e->getMessage());
        }

        return null;
    }
}
