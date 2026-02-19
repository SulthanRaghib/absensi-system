<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds `schedule_jam_masuk` to the absences table.
 *
 * WHY this column exists:
 * The "late / on-time" threshold for a given day comes from the Settings table
 * (normal schedule: 07:30, Ramadan schedule: e.g. 08:00).  Those settings will
 * change every year when the admin configures the next Ramadan period.  Without
 * snapshotting the threshold at the moment of check-in, historical reports will
 * be retroactively re-evaluated against the CURRENT settings, producing wrong
 * results (e.g. a 2026 Ramadan attendance appearing "late" when the 2027 Ramadan
 * dates are entered).
 *
 * Storing this value makes every absence record self-contained and immutable to
 * future setting changes.
 *
 * NULL = record was created before this feature existed; UI falls back to 07:30.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absences', function (Blueprint $table) {
            // e.g. "07:30" or "08:00" — the jam_masuk threshold active at check-in
            $table->string('schedule_jam_masuk', 5)->nullable()->after('jam_pulang')
                ->comment('Batas jam masuk (HH:MM) yang berlaku saat absen tercatat. NULL = sebelum fitur ini ditambahkan (fallback 07:30).');
        });
    }

    public function down(): void
    {
        Schema::table('absences', function (Blueprint $table) {
            $table->dropColumn('schedule_jam_masuk');
        });
    }
};
