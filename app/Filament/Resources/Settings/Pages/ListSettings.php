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
                ->label(fn() => (Setting::where('key', 'device_validation_enabled')->first()->value ?? '0') == '1' ? 'Device: ON' : 'Device: OFF')
                ->color(fn() => (Setting::where('key', 'device_validation_enabled')->first()->value ?? '0') == '1' ? 'success' : 'danger')
                ->icon(fn() => (Setting::where('key', 'device_validation_enabled')->first()->value ?? '0') == '1' ? 'heroicon-o-device-phone-mobile' : 'heroicon-o-device-tablet')
                ->requiresConfirmation()
                ->modalHeading(fn() => (Setting::where('key', 'device_validation_enabled')->first()->value ?? '0') == '1' ? 'Nonaktifkan Validasi Device?' : 'Aktifkan Validasi Device?')
                ->modalDescription(fn() => (Setting::where('key', 'device_validation_enabled')->first()->value ?? '0') == '1'
                    ? 'Apakah Anda yakin ingin menonaktifkan validasi device? Pegawai dapat login dan absen menggunakan device apa saja tanpa batasan.'
                    : 'Apakah Anda yakin ingin mengaktifkan validasi device? Pegawai hanya dapat absen menggunakan device yang terdaftar.')
                ->modalSubmitActionLabel(fn() => (Setting::where('key', 'device_validation_enabled')->first()->value ?? '0') == '1' ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan')
                ->modalIcon(fn() => (Setting::where('key', 'device_validation_enabled')->first()->value ?? '0') == '1' ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-check-circle')
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
                ->label(fn() => (Setting::where('key', 'face_recognition_enabled')->first()->value ?? '0') == '1' ? 'Face Rec: ON' : 'Face Rec: OFF')
                ->color(fn() => (Setting::where('key', 'face_recognition_enabled')->first()->value ?? '0') == '1' ? 'success' : 'danger')
                ->icon(fn() => (Setting::where('key', 'face_recognition_enabled')->first()->value ?? '0') == '1' ? 'heroicon-o-face-smile' : 'heroicon-o-face-frown')
                ->requiresConfirmation()
                ->modalHeading(fn() => (Setting::where('key', 'face_recognition_enabled')->first()->value ?? '0') == '1' ? 'Nonaktifkan Face Recognition?' : 'Aktifkan Face Recognition?')
                ->modalDescription(fn() => (Setting::where('key', 'face_recognition_enabled')->first()->value ?? '0') == '1'
                    ? 'Apakah Anda yakin ingin menonaktifkan validasi wajah? Pegawai dapat absen tanpa perlu verifikasi wajah.'
                    : 'Apakah Anda yakin ingin mengaktifkan validasi wajah? Pegawai harus melakukan verifikasi wajah saat absen.')
                ->modalSubmitActionLabel(fn() => (Setting::where('key', 'face_recognition_enabled')->first()->value ?? '0') == '1' ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan')
                ->modalIcon(fn() => (Setting::where('key', 'face_recognition_enabled')->first()->value ?? '0') == '1' ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-check-circle')
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

            CreateAction::make(),
        ];
    }
}
