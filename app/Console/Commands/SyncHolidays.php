<?php

namespace App\Console\Commands;

use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SyncHolidays extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-holidays {--year= : The year to fetch (defaults to current year)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch holidays from external API and sync to local database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $year = $this->option('year') ?? now()->year;

        $this->info("Fetching holidays for year: {$year}...");

        $url = "https://libur.deno.dev/api?year={$year}";

        try {
            $response = Http::timeout(10)->get($url);

            if ($response->failed()) {
                $this->error("Failed to fetch holidays: " . $response->status());
                return 1;
            }

            $holidays = $response->json();
            $data = is_array($holidays) ? $holidays : ($holidays['data'] ?? []);

            $count = 0;

            foreach ($data as $item) {
                if (empty($item['date'])) continue;

                try {
                    $date = Carbon::parse($item['date'])->toDateString();
                    $name = $item['name'] ?? 'Libur';
                    $isNational = true; // API mostly returns national holidays

                    Holiday::updateOrCreate(
                        ['date' => $date],
                        [
                            'name' => $name,
                            'is_national_holiday' => $isNational,
                            'description' => $item['description'] ?? null
                        ]
                    );

                    $count++;
                } catch (\Exception $e) {
                    $this->warn("Skipping invalid date: " . json_encode($item));
                }
            }

            // Also fetch for next year just in case we are near end of year
            if ($this->option('year') === null) {
                $nextYear = $year + 1;
                $this->info("Fetching holidays for next year: {$nextYear}...");
                // Basic implementation call recursively or duplicate logic here?
                // Let's just do a quick fetch for next year right here to keep it simple self-contained
                $this->call('app:sync-holidays', ['--year' => $nextYear]);
            }

            // Clear any caches in HolidayService
            // Based on previous code, cache key pattern was "holidays:{year}:{month}"
            Cache::flush(); // Simple flush, or target specific keys if possible

            $this->info("Successfully synced {$count} holidays for {$year}.");
            return 0;
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            Log::error('SyncHolidays command failed: ' . $e->getMessage());
            return 1;
        }
    }
}
