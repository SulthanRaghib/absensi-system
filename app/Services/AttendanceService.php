<?php

namespace App\Services;

use App\Models\Setting;
use Carbon\Carbon;

class AttendanceService
{
    // Hardcoded fallbacks – used only when DB settings are missing (e.g. fresh install).
    // The real defaults live in the `settings` table (keys: default_jam_masuk, etc.)
    // and are managed by the admin via the "Jadwal Jam Kerja Biasa" settings page.
    const DEFAULT_JAM_MASUK          = '07:30';
    const DEFAULT_JAM_PULANG         = '16:00';
    const DEFAULT_JAM_PULANG_JUMAT   = '16:30';

    /**
     * Read the normal schedule from DB (admin-configurable).
     * Caches the result for the request lifetime to avoid repeated DB hits.
     */
    protected function getDefaultSchedule(Carbon $forDate): array
    {
        static $cache = null;
        if ($cache === null) {
            $cache = Setting::getDefaultSchedule();
        }
        return [
            'jam_masuk'  => $cache['jam_masuk'],
            'jam_pulang' => $forDate->isFriday()
                ? $cache['jam_pulang_jumat']
                : $cache['jam_pulang'],
        ];
    }

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
            $normal       = $this->getDefaultSchedule(now());
            $jamMasukStr  = $normal['jam_masuk'];
            $jamPulangStr = $normal['jam_pulang'];
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
            $normal       = $this->getDefaultSchedule($date);
            $jamMasukStr  = $normal['jam_masuk'];
            $jamPulangStr = $normal['jam_pulang'];
        }

        return [
            'jam_masuk'        => $jamMasukStr,
            'jam_pulang'       => $jamPulangStr,
            'is_ramadan'       => $isRamadan,
            'jam_masuk_carbon' => Carbon::createFromFormat('H:i', $jamMasukStr),
        ];
    }
}
