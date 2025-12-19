<?php

namespace App\Observers;

use App\Filament\Resources\AttendanceCorrections\AttendanceCorrectionResource;
use App\Filament\User\Resources\AttendanceCorrections\AttendanceCorrectionResource as UserAttendanceCorrectionResource;
use App\Models\AttendanceCorrection;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class AttendanceCorrectionObserver
{
    /**
     * Handle the AttendanceCorrection "created" event.
     */
    public function created(AttendanceCorrection $attendanceCorrection): void
    {
        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            Notification::make()
                ->title('Pengajuan Koreksi Absen Baru')
                ->body("{$attendanceCorrection->user->name} mengajukan koreksi absen untuk tanggal {$attendanceCorrection->date->format('d M Y')}.")
                ->icon('heroicon-o-clipboard-document-check')
                ->iconColor('info')
                ->actions([
                    Action::make('Lihat')
                        ->url(AttendanceCorrectionResource::getUrl('edit', ['record' => $attendanceCorrection], panel: 'admin')),
                ])
                ->sendToDatabase($admin);
        }
    }

    /**
     * Handle the AttendanceCorrection "updated" event.
     */
    public function updated(AttendanceCorrection $attendanceCorrection): void
    {
        if ($attendanceCorrection->isDirty('status')) {
            if ($attendanceCorrection->status === 'approved') {
                Notification::make()
                    ->title('Koreksi Absen Disetujui ✅')
                    ->body("Koreksi absen Anda untuk tanggal {$attendanceCorrection->date->format('d M Y')} telah disetujui.")
                    ->success()
                    ->sendToDatabase($attendanceCorrection->user);
            } elseif ($attendanceCorrection->status === 'rejected') {
                Notification::make()
                    ->title('Koreksi Absen Ditolak ❌')
                    ->body("Maaf, koreksi absen Anda ditolak. Alasan: {$attendanceCorrection->rejection_note}.")
                    ->danger()
                    ->actions([
                        Action::make('Lihat')
                            ->url(UserAttendanceCorrectionResource::getUrl('edit', ['record' => $attendanceCorrection], panel: 'user')),
                    ])
                    ->sendToDatabase($attendanceCorrection->user);
            }
        }
    }
}
