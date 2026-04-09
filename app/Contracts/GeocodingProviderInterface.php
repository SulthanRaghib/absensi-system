<?php

namespace App\Contracts;

interface GeocodingProviderInterface
{
    /**
     * Reverse geocode coordinates to a formatted address string.
     *
     * @param float $lat
     * @param float $lng
     * @return string|null
     */
    public function reverseGeocode(float $lat, float $lng): ?string;
}
