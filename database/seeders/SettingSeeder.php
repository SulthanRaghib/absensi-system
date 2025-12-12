<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::updateOrCreate(
            ['key' => 'device_validation_enabled'],
            [
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Aktifkan validasi Device ID saat absen',
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'face_recognition_enabled'],
            [
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Aktifkan validasi Wajah (Face Recognition) saat absen',
            ]
        );
    }
}
