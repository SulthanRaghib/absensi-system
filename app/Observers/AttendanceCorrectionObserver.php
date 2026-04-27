<?php

namespace App\Observers;

use App\Models\AttendanceCorrection;
use App\Models\User;
use App\Services\NotificationService;

class AttendanceCorrectionObserver
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    /**
     * Handle the AttendanceCorrection "created" event.
     */
    public function created(AttendanceCorrection $attendanceCorrection): void
    {
        User::where('role', 'admin')
            ->get()
            ->each(fn(User $admin) => $this->notificationService->sendAttendanceCorrectionRequestNotification($attendanceCorrection, $admin));
    }

    /**
     * Handle the AttendanceCorrection "updated" event.
     */
    public function updated(AttendanceCorrection $attendanceCorrection): void
    {
        if ($attendanceCorrection->wasChanged('status')) {
            if ($attendanceCorrection->status === 'approved') {
                $this->notificationService->updateNotificationStatus(
                    referenceType: 'attendance_correction',
                    referenceId: $attendanceCorrection->id,
                    newStatus: 'approved',
                );

                $this->notificationService->sendAttendanceCorrectionStatusNotification($attendanceCorrection);
            } elseif ($attendanceCorrection->status === 'rejected') {
                $this->notificationService->updateNotificationStatus(
                    referenceType: 'attendance_correction',
                    referenceId: $attendanceCorrection->id,
                    newStatus: 'rejected',
                );

                $this->notificationService->sendAttendanceCorrectionStatusNotification($attendanceCorrection);
            }
        }
    }
}
