<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Seeds the default normal-day work schedule into the settings table.
 *
 * WHY: Previously these values were hardcoded PHP constants in AttendanceService.
 * Moving them to the DB allows the admin to change them from the UI without
 * touching any source code.
 *
 * Keys seeded:
 *   default_jam_masuk          07:30  — Mon–Thu check-in threshold
 *   default_jam_pulang         16:00  — Mon–Thu check-out time
 *   default_jam_pulang_jumat   16:30  — Friday check-out time (shorter day)
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $rows = [
            [
                'key'         => 'default_jam_masuk',
                'value'       => '07:30',
                'type'        => 'time',
                'description' => 'Jam masuk normal (Senin – Kamis). Format HH:MM.',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'key'         => 'default_jam_pulang',
                'value'       => '16:00',
                'type'        => 'time',
                'description' => 'Jam pulang normal (Senin – Kamis). Format HH:MM.',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'key'         => 'default_jam_pulang_jumat',
                'value'       => '16:30',
                'type'        => 'time',
                'description' => 'Jam pulang khusus hari Jumat. Format HH:MM.',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
        ];

        foreach ($rows as $row) {
            DB::table('settings')->updateOrInsert(
                ['key' => $row['key']],
                $row
            );
        }
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('key', [
            'default_jam_masuk',
            'default_jam_pulang',
            'default_jam_pulang_jumat',
        ])->delete();
    }
};
