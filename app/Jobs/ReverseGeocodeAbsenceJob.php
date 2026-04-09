<?php

namespace App\Jobs;

use App\Models\Absence;
use App\Services\Geocoding\GeocodingManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ReverseGeocodeAbsenceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $absenceId;
    public $type; // 'masuk' or 'pulang'

    /**
     * Create a new job instance.
     */
    public function __construct(int $absenceId, string $type = 'masuk')
    {
        $this->absenceId = $absenceId;
        $this->type = $type;
    }

    /**
     * Execute the job.
     */
    public function handle(GeocodingManager $manager): void
    {
        $absence = Absence::find($this->absenceId);
        if (!$absence) {
            return;
        }

        if ($this->type === 'masuk' && $absence->lat_masuk && $absence->lng_masuk) {
            if (!$absence->lokasi_masuk) {
                // Not yet geocoded
                $address = $manager->reverseGeocode((float) $absence->lat_masuk, (float) $absence->lng_masuk);
                if ($address) {
                    $absence->update(['lokasi_masuk' => $address]);
                }
            }
        } elseif ($this->type === 'pulang' && $absence->lat_pulang && $absence->lng_pulang) {
            if (!$absence->lokasi_pulang) {
                // Not yet geocoded
                $address = $manager->reverseGeocode((float) $absence->lat_pulang, (float) $absence->lng_pulang);
                if ($address) {
                    $absence->update(['lokasi_pulang' => $address]);
                }
            }
        }
    }
}
