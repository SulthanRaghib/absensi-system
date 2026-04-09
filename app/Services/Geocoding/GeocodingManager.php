<?php

namespace App\Services\Geocoding;

class GeocodingManager
{
    /**
     * Resolve the most optimal Geocoding provider based on environment setup
     * 
     * @return \App\Contracts\GeocodingProviderInterface
     */
    public function getProvider()
    {
        $googleKey = env('GOOGLE_MAPS_API_KEY_SERVER', env('GOOGLE_MAPS_API_KEY'));

        if (!empty($googleKey)) {
            // Priority: Google Maps API if API Key is set
            return new GoogleGeocodingProvider($googleKey);
        }

        // Fallback: Free Nominatim OpenStreetMap
        return new NominatimGeocodingProvider();
    }

    /**
     * Helper method to directly reverse geocode
     */
    public function reverseGeocode(float $lat, float $lng): ?string
    {
        return $this->getProvider()->reverseGeocode($lat, $lng);
    }
}
