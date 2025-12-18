<?php

namespace App\Observers;

use App\Filament\Resources\Permissions\PermissionResource;
use App\Filament\User\Resources\Permissions\PermissionResource as UserPermissionResource;
use App\Models\Permission;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

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
