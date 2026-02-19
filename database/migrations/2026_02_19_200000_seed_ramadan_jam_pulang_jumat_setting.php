<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add the `ramadan_jam_pulang_jumat` key to the settings table.
     *
     * During Ramadan, Fridays typically have a shorter work day (15:30 by default).
     * This key is checked by AttendanceService when building the schedule for a Friday
     * that falls within the Ramadan date range.
     *
     * Default: 15:30 — change via Admin → Settings → Jadwal Khusus Ramadan.
     */
    public function up(): void
    {
        DB::table('settings')->updateOrInsert(
            ['key' => 'ramadan_jam_pulang_jumat'],
            [
                'value'       => '15:30',
                'type'        => 'time',
                'description' => 'Jam pulang khusus hari Jumat selama Ramadan (lebih awal dari hari biasa Ramadan)',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('settings')->where('key', 'ramadan_jam_pulang_jumat')->delete();
    }
};
