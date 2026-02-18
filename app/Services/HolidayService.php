<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class HolidayService
{
    /**
     * Get holidays for a specific month and year.
     * Returns an associative array where key is date (Y-m-d) and value is holiday name.
     *
     * @param int $year
     * @param int $month
     * @return array<string, string>
     */
    public function getHolidays(int $year, int $month): array
    {
        $cacheKey = "holidays:{$year}:{$month}";
        $errorCacheKey = "holidays:error:{$year}:{$month}";

        // 1. Check for valid cache
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // 2. Check if we recently failed (Circuit Breaker / Cache Stampede Protection)
        if (Cache::has($errorCacheKey)) {
            return []; // Fail fast silently
        }

        // 3. Attempt to fetch
        return $this->fetchFromApi($year, $month, $cacheKey, $errorCacheKey);
    }

    /**
     * Fetch holidays from the external API.
     *
     * @param int $year
     * @param int $month
     * @param string $cacheKey
     * @param string $errorCacheKey
     * @return array
     */
    protected function fetchFromApi(int $year, int $month, string $cacheKey, string $errorCacheKey): array
    {
        $url = "https://libur.deno.dev/api?year={$year}&month={$month}";
        $holidayMap = [];

        try {
            // timeout reduced to 2 seconds for fail-fast
            $response = Http::timeout(2)->connectTimeout(2)->get($url);

            if ($response->successful()) {
                $data = $response->json();
                // Handle direct array response or wrapped in data
                $items = is_array($data) ? $data : ($data['data'] ?? []);

                foreach ($items as $item) {
                    if (isset($item['date'])) {
                        try {
                            $date = Carbon::parse($item['date'])->toDateString();
                            $name = $item['name'] ?? 'Libur';
                            $holidayMap[$date] = $name;
                        } catch (\Throwable $e) {
                            // Invalid date format, skip
                        }
                    }
                }

                // Cache successful result for 24 hours
                Cache::put($cacheKey, $holidayMap, 60 * 24);
                return $holidayMap;
            }

            // If response is not successful, treat as error
            throw new \Exception('API returned ' . $response->status());
        } catch (\Throwable $e) {
            // Log error (optional)
            // \Log::warning("Holiday API failed: " . $e->getMessage());

            // Circuit Breaker: Cache the failure for 5 minutes
            // This prevents retrying the slow/broken API on every request
            Cache::put($errorCacheKey, true, 5);

            return []; // Return empty array so dashboard keeps working
        }
    }
}
