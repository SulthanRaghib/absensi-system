<?php

namespace App\Services;

use App\Models\Setting;
use Carbon\Carbon;

class AttendanceService
{
    // Normal (non-Ramadan) schedule constants
    // These can later be moved to the `settings` table if dynamic control is needed.
    const DEFAULT_JAM_MASUK  = '07:30';
    const DEFAULT_JAM_PULANG = '16:00'; // Friday uses 16:30
    const DEFAULT_JAM_PULANG_JUMAT = '16:30';

    /**
     * Get the active schedule for today.
     *
     * Checks if today falls within the configured Ramadan date range.
     * If yes, returns the Ramadan-specific times. Otherwise returns defaults.
     *
     * @return array{
     *   jam_masuk: string,       // HH:MM format threshold for "tepat waktu"
     *   jam_pulang: string,      // HH:MM format
     *   is_ramadan: bool,        // Whether the Ramadan schedule is currently active
     *   jam_masuk_carbon: Carbon,
     * }
     */
    public function getTodaySchedule(): array
    {
        $today   = now()->startOfDay();
        $ramadan = Setting::getRamadanSettings();

        $isRamadan = false;

        if (
            $ramadan['start_date'] !== null &&
            $ramadan['end_date'] !== null &&
            $ramadan['jam_masuk'] !== null &&
            $ramadan['jam_pulang'] !== null
        ) {
            $isRamadan = $today->between($ramadan['start_date'], $ramadan['end_date']);
        }

        if ($isRamadan) {
            $jamMasukStr = $ramadan['jam_masuk'];
            $jamPulangStr = $ramadan['jam_pulang'];
        } else {
            $jamMasukStr  = self::DEFAULT_JAM_MASUK;
            $jamPulangStr = now()->isFriday()
                ? self::DEFAULT_JAM_PULANG_JUMAT
                : self::DEFAULT_JAM_PULANG;
        }

        return [
            'jam_masuk'         => $jamMasukStr,
            'jam_pulang'        => $jamPulangStr,
            'is_ramadan'        => $isRamadan,
            // Pre-built Carbon object for direct comparison in controllers
            'jam_masuk_carbon'  => Carbon::createFromFormat('H:i', $jamMasukStr),
        ];
    }

    /**
     * Determine if a given Carbon datetime represents a "late" check-in.
     *
     * @param Carbon $checkInTime   The actual check-in time.
     * @return bool True = late (Terlambat), False = on time (Tepat Waktu)
     */
    public function isLate(Carbon $checkInTime): bool
    {
        $schedule = $this->getTodaySchedule();
        $threshold = $schedule['jam_masuk_carbon'];

        // Use seconds=59 so anything after HH:MM:00 is considered late
        $threshold->second(59);

        return $checkInTime->gt($threshold);
    }

    /**
     * Get the schedule for a specific historical date (used by the Calendar Widget).
     *
     * This allows the calendar to render accurate late/on-time labels for any past date,
     * respecting any Ramadan periods that were configured in the system.
     *
     * @param Carbon $date
     * @return array{jam_masuk: string, jam_pulang: string, is_ramadan: bool, jam_masuk_carbon: Carbon}
     */
    public function getScheduleForDate(Carbon $date): array
    {
        $day     = $date->copy()->startOfDay();
        $ramadan = Setting::getRamadanSettings();

        $isRamadan = false;

        if (
            $ramadan['start_date'] !== null &&
            $ramadan['end_date'] !== null &&
            $ramadan['jam_masuk'] !== null &&
            $ramadan['jam_pulang'] !== null
        ) {
            $isRamadan = $day->between($ramadan['start_date'], $ramadan['end_date']);
        }

        if ($isRamadan) {
            $jamMasukStr  = $ramadan['jam_masuk'];
            $jamPulangStr = $ramadan['jam_pulang'];
        } else {
            $jamMasukStr  = self::DEFAULT_JAM_MASUK;
            $jamPulangStr = $date->isFriday()
                ? self::DEFAULT_JAM_PULANG_JUMAT
                : self::DEFAULT_JAM_PULANG;
        }

        return [
            'jam_masuk'        => $jamMasukStr,
            'jam_pulang'       => $jamPulangStr,
            'is_ramadan'       => $isRamadan,
            'jam_masuk_carbon' => Carbon::createFromFormat('H:i', $jamMasukStr),
        ];
    }
}
