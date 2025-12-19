<?php

namespace App\Observers;

use App\Filament\Resources\Permissions\PermissionResource;
use App\Filament\User\Resources\Permissions\PermissionResource as UserPermissionResource;
use App\Models\Absence;
use App\Models\Permission;
use App\Models\User;
use Carbon\CarbonPeriod;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Schema;

class PermissionObserver
{
    /**
     * Handle the Permission "created" event.
     */
    public function created(Permission $permission): void
    {
        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            Notification::make()
                ->title('Pengajuan Izin Baru')
                ->body("{$permission->user->name} mengajukan izin {$permission->type}.")
                ->icon('heroicon-o-document-text')
                ->iconColor('info')
                ->actions([
                    Action::make('Lihat')
                        ->url(PermissionResource::getUrl('edit', ['record' => $permission], panel: 'admin')),
                ])
                ->sendToDatabase($admin);
        }
    }

    /**
     * Handle the Permission "updated" event.
     */
    public function updated(Permission $permission): void
    {
        if ($permission->isDirty('status')) {
            if ($permission->status === 'approved') {
                Notification::make()
                    ->title('Pengajuan Izin Disetujui ✅')
                    ->body("Izin {$permission->type} Anda untuk tanggal {$permission->start_date} telah disetujui.")
                    ->success()
                    ->sendToDatabase($permission->user);

                if ($permission->type === 'dinas_luar' && $permission->start_date && $permission->end_date) {
                    $hasStatusColumn = Schema::hasColumn('absences', 'status');
                    $hasRemarksColumn = Schema::hasColumn('absences', 'remarks');
                    $hasIsLateColumn = Schema::hasColumn('absences', 'is_late');

                    $period = CarbonPeriod::create($permission->start_date, $permission->end_date);

                    foreach ($period as $date) {
                        if ($date->isWeekend()) {
                            continue;
                        }

                        $checkOutTime = $date->isFriday() ? '16:30:00' : '16:00:00';

                        $values = [
                            'jam_masuk' => '07:30:00',
                            'jam_pulang' => $checkOutTime,
                        ];

                        if ($hasStatusColumn) {
                            $values['status'] = 'Hadir';
                        }

                        if ($hasRemarksColumn) {
                            $values['remarks'] = 'Otomatis dari Izin Dinas Luar';
                        }

                        if ($hasIsLateColumn) {
                            $values['is_late'] = false;
                        }

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
                Notification::make()
                    ->title('Pengajuan Izin Ditolak ❌')
                    ->body("Maaf, izin Anda ditolak. Alasan: {$permission->rejection_note}.")
                    ->danger()
                    ->actions([
                        Action::make('Lihat')
                            ->url(UserPermissionResource::getUrl('edit', ['record' => $permission], panel: 'user')),
                    ])
                    ->sendToDatabase($permission->user);
            }
        }
    }
}
