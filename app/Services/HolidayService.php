<?php

namespace App\Services;

use App\Models\Holiday;
use Illuminate\Support\Facades\Cache;

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

        // Cache for 24 hours to reduce DB load slightly, though DB is fast enough
        return Cache::remember($cacheKey, 60 * 24, function () use ($year, $month) {
            return Holiday::whereYear('date', $year)
                ->whereMonth('date', $month)
                ->select('date', 'name')
                ->get()
                ->mapWithKeys(function ($holiday) {
                    return [$holiday->date->toDateString() => $holiday->name];
                })
                ->toArray();
        });
    }

    /**
     * Fetch holidays from the external API.
     * DEPRECATED: Use artisan app:sync-holidays instead.
     */
    protected function fetchFromApi(int $year, int $month, string $cacheKey, string $errorCacheKey): array
    {
        // This method is no longer used for live traffic to prevent timeouts.
        // It remains here only as a fallback or for historical reference if needed,
        // but strictly returns empty array to enforce DB usage.
        return [];
    }
}
