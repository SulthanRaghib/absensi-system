<?php

namespace App\Services;

use App\Models\AttendanceCorrection;
use App\Models\Permission;
use App\Models\Notification;
use App\Models\User;
use App\Filament\Resources\AttendanceCorrections\AttendanceCorrectionResource;
use App\Filament\Resources\Permissions\PermissionResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;

class NotificationService
{
    /**
     * Send permission request notification to an admin and attach metadata.
     */
    public function sendPermissionRequestNotification(Permission $permission, User $admin): bool
    {
        FilamentNotification::make()
            ->title('Pengajuan Izin Baru')
            ->body("{$permission->user->name} mengajukan izin {$permission->type}.")
            ->icon('heroicon-o-document-text')
            ->iconColor('info')
            ->actions([
                Action::make('Lihat')
                    ->url(PermissionResource::getUrl('edit', ['record' => $permission], panel: 'admin')),
            ])
            ->sendToDatabase($admin);

        return $this->attachMetadataToLatestDatabaseNotification(
            userId: $admin->id,
            referenceType: Notification::REFERENCE_PERMISSION,
            referenceId: $permission->id,
        );
    }

    /**
     * Send attendance correction notification to an admin and attach metadata.
     */
    public function sendAttendanceCorrectionRequestNotification(AttendanceCorrection $attendanceCorrection, User $admin): bool
    {
        FilamentNotification::make()
            ->title('Pengajuan Koreksi Absen Baru')
            ->body("{$attendanceCorrection->user->name} mengajukan koreksi absen untuk tanggal {$attendanceCorrection->date->format('d M Y')}.")
            ->icon('heroicon-o-clipboard-document-check')
            ->iconColor('info')
            ->actions([
                Action::make('Lihat')
                    ->url(AttendanceCorrectionResource::getUrl('edit', ['record' => $attendanceCorrection], panel: 'admin')),
            ])
            ->sendToDatabase($admin);

        return $this->attachMetadataToLatestDatabaseNotification(
            userId: $admin->id,
            referenceType: Notification::REFERENCE_ATTENDANCE_CORRECTION,
            referenceId: $attendanceCorrection->id,
        );
    }

    /**
     * Send user-facing approval/rejection notification for permission.
     */
    public function sendPermissionStatusNotification(Permission $permission): void
    {
        $notification = FilamentNotification::make();

        if ($permission->status === Notification::STATUS_APPROVED) {
            $notification
                ->title('Pengajuan Izin Disetujui ✅')
                ->body("Izin {$permission->type} Anda untuk tanggal {$permission->start_date} telah disetujui.")
                ->success();
        } else {
            $notification
                ->title('Pengajuan Izin Ditolak ❌')
                ->body("Maaf, izin Anda ditolak. Alasan: {$permission->rejection_note}.")
                ->danger()
                ->actions([
                    Action::make('Lihat')
                        ->url(
                            \App\Filament\User\Resources\Permissions\PermissionResource::getUrl(
                                'edit',
                                ['record' => $permission],
                                panel: 'user'
                            )
                        ),
                ]);
        }

        $notification->sendToDatabase($permission->user);
    }

    /**
     * Send user-facing approval/rejection notification for attendance correction.
     */
    public function sendAttendanceCorrectionStatusNotification(AttendanceCorrection $attendanceCorrection): void
    {
        $notification = FilamentNotification::make();

        if ($attendanceCorrection->status === Notification::STATUS_APPROVED) {
            $notification
                ->title('Koreksi Absen Disetujui ✅')
                ->body("Koreksi absen Anda untuk tanggal {$attendanceCorrection->date->format('d M Y')} telah disetujui.")
                ->success();
        } else {
            $notification
                ->title('Koreksi Absen Ditolak ❌')
                ->body("Maaf, koreksi absen Anda ditolak. Alasan: {$attendanceCorrection->rejection_note}.")
                ->danger()
                ->actions([
                    Action::make('Lihat')
                        ->url(
                            \App\Filament\User\Resources\AttendanceCorrections\AttendanceCorrectionResource::getUrl(
                                'edit',
                                ['record' => $attendanceCorrection],
                                panel: 'user'
                            )
                        ),
                ]);
        }

        $notification->sendToDatabase($attendanceCorrection->user);
    }

    /**
     * Archive notification ketika Permission/Correction sudah diproses.
     */
    public function updateNotificationStatus(
        string $referenceType,
        int $referenceId,
        string $newStatus,
    ): int {
        return Notification::where('reference_type', $referenceType)
            ->where('reference_id', $referenceId)
            ->update([
                'status' => $newStatus,
                'is_cleared' => true,
            ]);
    }

    private function attachMetadataToLatestDatabaseNotification(
        int $userId,
        string $referenceType,
        int $referenceId,
    ): bool {
        $notification = Notification::query()
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $userId)
            ->whereNull('reference_type')
            ->latest('created_at')
            ->first();

        if (! $notification) {
            return false;
        }

        return $notification->update([
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'status' => Notification::STATUS_PENDING,
            'is_cleared' => false,
        ]);
    }
}
