<?php

namespace App\Services;

use App\Models\Setting;
use Jenssegers\Agent\Agent;

class GeoLocationService
{
    /**
     * Calculate distance between two coordinates using Haversine formula
     *
     * @param float $lat1 Latitude point 1
     * @param float $lon1 Longitude point 1
     * @param float $lat2 Latitude point 2
     * @param float $lon2 Longitude point 2
     * @return float Distance in meters
     */
    public function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // Earth radius in meters

        // Convert degrees to radians
        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $deltaLatRad = deg2rad($lat2 - $lat1);
        $deltaLonRad = deg2rad($lon2 - $lon1);

        // Haversine formula
        $a = sin($deltaLatRad / 2) * sin($deltaLatRad / 2) +
            cos($lat1Rad) * cos($lat2Rad) *
            sin($deltaLonRad / 2) * sin($deltaLonRad / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $distance = $earthRadius * $c;

        return round($distance, 2);
    }

    /**
     * Validate if user location is within office radius
     *
     * @param float $userLat User latitude
     * @param float $userLon User longitude
     * @return array ['valid' => bool, 'distance' => float, 'message' => string]
     */
    public function validateLocation(float $userLat, float $userLon): array
    {
        $office = Setting::getOfficeLocation();

        $distance = $this->calculateDistance(
            $userLat,
            $userLon,
            $office['latitude'],
            $office['longitude']
        );

        // Check if radius is enabled
        if (!Setting::isRadiusEnabled()) {
            return [
                'valid' => true,
                'distance' => $distance,
                'message' => "Radius check disabled. Jarak: {$distance} meter.",
                'office_location' => $office,
            ];
        }

        $isValid = $distance <= $office['radius'];

        return [
            'valid' => $isValid,
            'distance' => $distance,
            'message' => $isValid
                ? "Lokasi valid. Jarak: {$distance} meter dari kantor."
                : "Lokasi terlalu jauh! Jarak: {$distance} meter. Maksimal: {$office['radius']} meter.",
            'office_location' => $office,
        ];
    }

    /**
     * Validate GPS accuracy
     *
     * @param float $accuracy Accuracy in meters
     * @param float $maxAccuracy Maximum allowed accuracy (default: 100m)
     * @return array
     */
    public function validateAccuracy(float $accuracy, float $maxAccuracy = 10000): array
    {
        // If radius check is disabled, we also disable strict accuracy check
        // allowing users to check in from anywhere even with poor GPS (e.g. PC/Laptop without GPS)
        if (!Setting::isRadiusEnabled()) {
            return [
                'valid' => true,
                'accuracy' => $accuracy,
                'message' => "Akurasi GPS diabaikan (Radius Check Disabled). Akurasi: {$accuracy} meter.",
            ];
        }

        $isValid = $accuracy <= $maxAccuracy;

        return [
            'valid' => $isValid,
            'accuracy' => $accuracy,
            'message' => $isValid
                ? "Akurasi GPS baik: {$accuracy} meter."
                : "Akurasi GPS terlalu buruk: {$accuracy} meter. Harap aktifkan GPS dengan baik.",
        ];
    }

    /**
     * Get device information from request
     */
    public function getDeviceInfo(\Illuminate\Http\Request $request): string
    {
        $agent = new Agent();
        $agent->setUserAgent($request->userAgent());

        $device = $agent->device();
        $platform = $agent->platform();
        $browser = $agent->browser();
        $version = $agent->version($browser);

        $deviceType = 'Unknown';
        if ($agent->isDesktop()) {
            $deviceType = 'Desktop';
        } elseif ($agent->isTablet()) {
            $deviceType = 'Tablet';
        } elseif ($agent->isPhone()) {
            $deviceType = 'Phone';
        } elseif ($agent->isRobot()) {
            $deviceType = 'Robot';
        }

        $info = "{$platform} | {$browser} {$version} | {$device} ({$deviceType})";

        // Add IP address
        $ip = $request->ip();
        $info .= " | IP: {$ip}";

        return substr($info, 0, 255);
    }
}
