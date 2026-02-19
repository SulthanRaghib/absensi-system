<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Seed the 4 Ramadan schedule setting keys into the existing key-value settings table.
     * These all default to NULL so the feature is disabled until an admin configures it.
     */
    public function up(): void
    {
        $settings = [
            [
                'key'         => 'ramadan_start_date',
                'value'       => null,
                'type'        => 'date',
                'description' => 'Tanggal mulai jadwal Ramadan',
            ],
            [
                'key'         => 'ramadan_end_date',
                'value'       => null,
                'type'        => 'date',
                'description' => 'Tanggal selesai jadwal Ramadan',
            ],
            [
                'key'         => 'ramadan_jam_masuk',
                'value'       => null,
                'type'        => 'time',
                'description' => 'Jam masuk khusus Ramadan',
            ],
            [
                'key'         => 'ramadan_jam_pulang',
                'value'       => null,
                'type'        => 'time',
                'description' => 'Jam pulang khusus Ramadan',
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                array_merge($setting, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('key', [
            'ramadan_start_date',
            'ramadan_end_date',
            'ramadan_jam_masuk',
            'ramadan_jam_pulang',
        ])->delete();
    }
};
