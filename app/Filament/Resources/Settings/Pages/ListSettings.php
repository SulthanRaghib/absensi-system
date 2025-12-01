<?php

namespace App\Filament\Resources\Settings\Pages;

use App\Filament\Resources\Settings\SettingResource;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListSettings extends ListRecords
{
    protected static string $resource = SettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('toggleRadius')
                ->label(fn() => Setting::isRadiusEnabled() ? 'Radius: ON' : 'Radius: OFF')
                ->color(fn() => Setting::isRadiusEnabled() ? 'success' : 'danger')
                ->icon(fn() => Setting::isRadiusEnabled() ? 'heroicon-o-map-pin' : 'heroicon-o-map')
                ->requiresConfirmation()
                ->modalHeading(fn() => Setting::isRadiusEnabled() ? 'Nonaktifkan Radius?' : 'Aktifkan Radius?')
                ->modalDescription(fn() => Setting::isRadiusEnabled()
                    ? 'Apakah Anda yakin ingin menonaktifkan pengecekan radius? Pegawai akan dapat melakukan absensi dari lokasi mana pun tanpa batasan jarak.'
                    : 'Apakah Anda yakin ingin mengaktifkan pengecekan radius? Pegawai hanya dapat melakukan absensi jika berada di dalam area jangkauan kantor.')
                ->modalSubmitActionLabel(fn() => Setting::isRadiusEnabled() ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan')
                ->modalIcon(fn() => Setting::isRadiusEnabled() ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-check-circle')
                ->action(function () {
                    $current = Setting::isRadiusEnabled();
                    Setting::set('radius_enabled', !$current ? 'true' : 'false', 'boolean');

                    Notification::make()
                        ->title('Pengaturan Radius Diperbarui')
                        ->body('Status pengecekan radius berhasil diubah.')
                        ->success()
                        ->send();

                    $this->redirect(request()->header('Referer'));
                }),
            CreateAction::make(),
        ];
    }
}
