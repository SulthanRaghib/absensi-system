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

            Action::make('toggleDeviceValidation')
                ->label(fn() => $this->isDeviceValidationEnabled() ? 'Device: ON' : 'Device: OFF')
                ->color(fn() => $this->isDeviceValidationEnabled() ? 'success' : 'danger')
                ->icon(fn() => $this->isDeviceValidationEnabled() ? 'heroicon-o-device-phone-mobile' : 'heroicon-o-device-tablet')
                ->requiresConfirmation()
                ->modalHeading(fn() => $this->isDeviceValidationEnabled() ? 'Nonaktifkan Validasi Device?' : 'Aktifkan Validasi Device?')
                ->modalDescription(fn() => $this->isDeviceValidationEnabled()
                    ? 'Apakah Anda yakin ingin menonaktifkan validasi device? Pegawai dapat login dan absen menggunakan device apa saja tanpa batasan.'
                    : 'Apakah Anda yakin ingin mengaktifkan validasi device? Pegawai hanya dapat absen menggunakan device yang terdaftar.')
                ->modalSubmitActionLabel(fn() => $this->isDeviceValidationEnabled() ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan')
                ->modalIcon(fn() => $this->isDeviceValidationEnabled() ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-check-circle')
                ->action(function () {
                    $setting = Setting::firstOrCreate(
                        ['key' => 'device_validation_enabled'],
                        ['type' => 'boolean', 'description' => 'Aktifkan validasi Device ID saat absen']
                    );

                    $current = $setting->value == '1';
                    $setting->update(['value' => $current ? '0' : '1']);

                    Notification::make()
                        ->title('Pengaturan Device Validation Diperbarui')
                        ->body('Status validasi device berhasil diubah.')
                        ->success()
                        ->send();

                    $this->redirect(request()->header('Referer'));
                }),

            Action::make('toggleFaceRecognition')
                ->label(fn() => $this->isFaceRecognitionEnabled() ? 'Face Rec: ON' : 'Face Rec: OFF')
                ->color(fn() => $this->isFaceRecognitionEnabled() ? 'success' : 'danger')
                ->icon(fn() => $this->isFaceRecognitionEnabled() ? 'heroicon-o-face-smile' : 'heroicon-o-face-frown')
                ->requiresConfirmation()
                ->modalHeading(fn() => $this->isFaceRecognitionEnabled() ? 'Nonaktifkan Face Recognition?' : 'Aktifkan Face Recognition?')
                ->modalDescription(fn() => $this->isFaceRecognitionEnabled()
                    ? 'Apakah Anda yakin ingin menonaktifkan validasi wajah? Pegawai dapat absen tanpa perlu verifikasi wajah.'
                    : 'Apakah Anda yakin ingin mengaktifkan validasi wajah? Pegawai harus melakukan verifikasi wajah saat absen.')
                ->modalSubmitActionLabel(fn() => $this->isFaceRecognitionEnabled() ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan')
                ->modalIcon(fn() => $this->isFaceRecognitionEnabled() ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-check-circle')
                ->action(function () {
                    $setting = Setting::firstOrCreate(
                        ['key' => 'face_recognition_enabled'],
                        ['type' => 'boolean', 'description' => 'Aktifkan validasi Wajah (Face Recognition) saat absen']
                    );

                    $current = $setting->value == '1';
                    $setting->update(['value' => $current ? '0' : '1']);

                    Notification::make()
                        ->title('Pengaturan Face Recognition Diperbarui')
                        ->body('Status validasi wajah berhasil diubah.')
                        ->success()
                        ->send();

                    $this->redirect(request()->header('Referer'));
                }),

            Action::make('ramadanSettings')
                ->label('Jadwal Ramadan')
                ->color('warning')
                ->icon('heroicon-o-moon')
                ->url(fn() => SettingResource::getUrl('ramadan-settings')),

            Action::make('jadwalBiasa')
                ->label('Jam Kerja Normal')
                ->color('primary')
                ->icon('heroicon-o-clock')
                ->url(fn() => SettingResource::getUrl('jadwal-biasa')),

            CreateAction::make(),
        ];
    }

    protected function isDeviceValidationEnabled(): bool
    {
        return Setting::isDeviceValidationEnabled();
    }

    protected function isFaceRecognitionEnabled(): bool
    {
        return Setting::isFaceRecognitionEnabled();
    }
}
