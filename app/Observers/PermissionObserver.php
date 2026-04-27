<?php

namespace App\Observers;

use App\Models\Absence;
use App\Models\Permission;
use App\Models\User;
use App\Services\AttendanceService;
use App\Services\NotificationService;
use Carbon\CarbonPeriod;


class PermissionObserver
{
    public function __construct(
        private readonly NotificationService $notificationService,
        private readonly AttendanceService $attendanceService,
    ) {}

    /**
     * Handle the Permission "created" event.
     */
    public function created(Permission $permission): void
    {
        User::where('role', 'admin')
            ->get()
            ->each(fn(User $admin) => $this->notificationService->sendPermissionRequestNotification($permission, $admin));
    }

    /**
     * Handle the Permission "updated" event.
     */
    public function updated(Permission $permission): void
    {
        if ($permission->wasChanged('status')) {
            if ($permission->status === 'approved') {
                $this->notificationService->updateNotificationStatus(
                    referenceType: 'permission',
                    referenceId: $permission->id,
                    newStatus: 'approved',
                );

                $this->notificationService->sendPermissionStatusNotification($permission);

                if ($permission->type === 'dinas_luar' && $permission->start_date && $permission->end_date) {
                    $period = CarbonPeriod::create($permission->start_date, $permission->end_date);

                    foreach ($period as $date) {
                        if ($date->isWeekend()) {
                            continue;
                        }

                        $daySchedule  = $this->attendanceService->getScheduleForDate($date);
                        $checkInTime  = $date->format('Y-m-d') . ' ' . $daySchedule['jam_masuk'] . ':00';
                        $checkOutTime = $date->format('Y-m-d') . ' ' . $daySchedule['jam_pulang'] . ':00';

                        $values = [
                            'jam_masuk'          => $checkInTime,
                            'jam_pulang'         => $checkOutTime,
                            'schedule_jam_masuk' => $daySchedule['jam_masuk'],
                            'is_ramadan'         => $daySchedule['is_ramadan'],
                        ];

                        Absence::unguarded(function () use ($permission, $date, $values) {
                            Absence::updateOrCreate(
                                [
                                    'user_id' => $permission->user_id,
                                    'tanggal' => $date->toDateString(),
                                ],
                                $values
                            );
                        });
                    }
                }
            } elseif ($permission->status === 'rejected') {
                $this->notificationService->updateNotificationStatus(
                    referenceType: 'permission',
                    referenceId: $permission->id,
                    newStatus: 'rejected',
                );

                $this->notificationService->sendPermissionStatusNotification($permission);
            }
        }
    }
}
