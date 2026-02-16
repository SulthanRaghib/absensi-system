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

        // Cache for 24 hours (1440 minutes)
        return Cache::remember($cacheKey, 60 * 24, function () use ($year, $month) {
            return $this->fetchFromApi($year, $month);
        });
    }

    /**
     * Fetch holidays from the external API.
     *
     * @param int $year
     * @param int $month
     * @return array
     */
    protected function fetchFromApi(int $year, int $month): array
    {
        $url = "https://libur.deno.dev/api?year={$year}&month={$month}";
        $holidayMap = [];

        try {
            $response = Http::timeout(5)->get($url);

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
            }
        } catch (\Throwable $e) {
            // Log error or fail silently, returning empty array
            // \Log::error("Failed to fetch holidays: " . $e->getMessage());
        }

        return $holidayMap;
    }
}
