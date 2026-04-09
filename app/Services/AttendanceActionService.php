<?php

namespace App\Services;

use App\Models\Absence;
use App\Models\User;
use Carbon\Carbon;

class AttendanceActionService
{
    public function __construct(
        protected GeoLocationService $geoService,
        protected AttendanceService $attendanceService,
    ) {}

    /**
     * Shared check-in workflow used by both AbsensiController and DirectAttendanceController.
     *
     * Returns a normalized payload:
     * - ok: bool
     * - status: int (http-like status code)
     * - message: string
     * - distance?: float
     * - absence?: Absence
     * - schedule?: array
     * - statusLabel?: string
     */
    public function checkIn(
        User $user,
        float $latitude,
        float $longitude,
        float $accuracy,
        string $riskLevel,
        string $deviceInfo,
        ?string $imagePath = null,
        ?Carbon $now = null,
    ): array {
        $now ??= now();

        // If user already has a valid check-in today, block duplicate.
        if (Absence::hasCheckedInToday($user->id)) {
            return [
                'ok' => false,
                'status' => 400,
                'message' => 'Anda sudah melakukan absen masuk hari ini.',
            ];
        }

        $geo = $this->validateGeo($latitude, $longitude, $accuracy);
        if (! $geo['ok']) {
            return $geo;
        }

        $schedule = $this->attendanceService->getTodaySchedule();
        $statusLabel = $this->attendanceService->isLate($now) ? 'Terlambat' : 'Tepat Waktu';

        // Handle edge-case record (created manually) to avoid duplicate unique key.
        $todayRecord = Absence::getTodayAbsence($user->id);

        if ($todayRecord && ! $todayRecord->jam_masuk) {
            $todayRecord->update([
                'jam_masuk'          => $now,
                'schedule_jam_masuk' => $schedule['jam_masuk'],
                'is_ramadan'         => $schedule['is_ramadan'],
                'lat_masuk'          => $latitude,
                'lng_masuk'          => $longitude,
                'distance_masuk'     => $geo['distance'],
                'device_info'        => $deviceInfo,
                'capture_image'      => $imagePath,
                'risk_level'         => $riskLevel,
            ]);

            $absence = $todayRecord->fresh();
        } else {
            $absence = Absence::create([
                'user_id'            => $user->id,
                'tanggal'            => $now->toDateString(),
                'jam_masuk'          => $now,
                'schedule_jam_masuk' => $schedule['jam_masuk'],
                'is_ramadan'         => $schedule['is_ramadan'],
                'lat_masuk'          => $latitude,
                'lng_masuk'          => $longitude,
                'distance_masuk'     => $geo['distance'],
                'device_info'        => $deviceInfo,
                'capture_image'      => $imagePath,
                'risk_level'         => $riskLevel,
            ]);
        }

        // Dispatch Background Geocoding Job
        if ($absence) {
            \App\Jobs\ReverseGeocodeAbsenceJob::dispatch($absence->id, 'masuk');
        }

        return [
            'ok' => true,
            'status' => 200,
            'message' => 'Absen masuk berhasil! ' . $geo['message'],
            'distance' => $geo['distance'],
            'absence' => $absence,
            'schedule' => $schedule,
            'statusLabel' => $statusLabel,
        ];
    }

    /**
     * Shared check-out workflow used by both AbsensiController and DirectAttendanceController.
     */
    public function checkOut(
        User $user,
        float $latitude,
        float $longitude,
        float $accuracy,
        ?Carbon $now = null,
    ): array {
        $now ??= now();

        if (! Absence::hasCheckedInToday($user->id)) {
            return [
                'ok' => false,
                'status' => 400,
                'message' => 'Anda belum melakukan absen masuk hari ini.',
            ];
        }

        if (Absence::hasCheckedOutToday($user->id)) {
            return [
                'ok' => false,
                'status' => 400,
                'message' => 'Anda sudah melakukan absen pulang hari ini.',
            ];
        }

        $geo = $this->validateGeo($latitude, $longitude, $accuracy);
        if (! $geo['ok']) {
            return $geo;
        }

        $absence = Absence::getTodayAbsence($user->id);
        $absence->update([
            'jam_pulang'       => $now,
            'lat_pulang'       => $latitude,
            'lng_pulang'       => $longitude,
            'distance_pulang'  => $geo['distance'],
        ]);

        $freshAbsence = $absence->fresh();

        // Dispatch Background Geocoding Job
        if ($freshAbsence) {
            \App\Jobs\ReverseGeocodeAbsenceJob::dispatch($freshAbsence->id, 'pulang');
        }

        return [
            'ok' => true,
            'status' => 200,
            'message' => 'Absen pulang berhasil! ' . $geo['message'],
            'distance' => $geo['distance'],
            'absence' => $freshAbsence,
        ];
    }

    protected function validateGeo(float $latitude, float $longitude, float $accuracy): array
    {
        $accuracyCheck = $this->geoService->validateAccuracy($accuracy);
        if (! $accuracyCheck['valid']) {
            return [
                'ok' => false,
                'status' => 400,
                'message' => $accuracyCheck['message'],
            ];
        }

        $locationCheck = $this->geoService->validateLocation($latitude, $longitude);
        if (! $locationCheck['valid']) {
            return [
                'ok' => false,
                'status' => 400,
                'message' => $locationCheck['message'],
                'distance' => $locationCheck['distance'],
            ];
        }

        return [
            'ok' => true,
            'distance' => $locationCheck['distance'],
            'message' => $locationCheck['message'],
        ];
    }
}
