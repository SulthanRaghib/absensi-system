<?php

namespace App\Services;

use App\Models\Absence;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserDevice;
use Carbon\CarbonInterface;

class DeviceRiskService
{
    /**
     * Assess risk level for a check-in attempt and synchronize related users' risk labels.
     *
     * Daily-reset behavior:
     * - Collision is calculated from users who used the SAME device TODAY only.
     * - Historical device ownership in user_devices is kept for audit trail,
     *   but it does not force danger/warning on a new day when no collision happens.
     */
    public function assessForCheckIn(User $user, string $deviceToken, ?CarbonInterface $forDate = null): string
    {
        $date = ($forDate ? $forDate->copy() : now())->startOfDay();

        // Always record device usage history (audit trail).
        $userDevice = UserDevice::firstOrCreate(
            ['user_id' => $user->id, 'device_unique_id' => $deviceToken],
            ['last_used_at' => now()]
        );
        $userDevice->update(['last_used_at' => now()]);

        $isDeviceValidationEnabled = Setting::isDeviceValidationEnabled();

        if (! $isDeviceValidationEnabled) {
            return 'safe';
        }

        // Daily collision: only users that used this device today are considered.
        $todayDeviceUsers = UserDevice::where('device_unique_id', $deviceToken)
            ->whereDate('last_used_at', $date->toDateString())
            ->orderBy('last_used_at', 'asc')
            ->get();

        $uniqueUserIds = $todayDeviceUsers->pluck('user_id')->unique()->values();

        if ($uniqueUserIds->count() <= 1) {
            return 'safe';
        }

        $firstUserTodayId = (int) ($todayDeviceUsers->first()?->user_id ?? 0);

        if ($firstUserTodayId === (int) $user->id) {
            // Owner-like behavior for today: current user stays safe.
            // Other users who already checked in today with this device become danger.
            $borrowerIds = $uniqueUserIds
                ->filter(fn($id) => (int) $id !== (int) $user->id)
                ->all();

            if (! empty($borrowerIds)) {
                Absence::whereIn('user_id', $borrowerIds)
                    ->whereDate('tanggal', $date->toDateString())
                    ->where('risk_level', '!=', 'danger')
                    ->update(['risk_level' => 'danger']);
            }

            return 'safe';
        }

        // Borrower behavior for today: current user is danger.
        // Mark today's first user as warning (unless already danger).
        if ($firstUserTodayId > 0) {
            Absence::where('user_id', $firstUserTodayId)
                ->whereDate('tanggal', $date->toDateString())
                ->where('risk_level', '!=', 'danger')
                ->update(['risk_level' => 'warning']);
        }

        return 'danger';
    }
}
