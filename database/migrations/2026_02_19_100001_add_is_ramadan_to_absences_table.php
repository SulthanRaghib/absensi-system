<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds `is_ramadan` flag to the absences table.
 *
 * WHY: We need to permanently tag whether a given absence was recorded under the
 * Ramadan schedule.  We cannot derive this reliably from the date alone because
 * the admin may update Ramadan period settings next year (different dates).
 * Storing the flag at check-in time makes every record self-describing.
 *
 * This flag drives:
 *   - "🌙 Ramadan" badge in the admin Absence list
 *   - "Absen Ramadan" indicator in the Excel export
 *   - Calendar widget tooltip
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absences', function (Blueprint $table) {
            $table->boolean('is_ramadan')
                ->default(false)
                ->after('schedule_jam_masuk')
                ->comment('True when the absence was recorded while the Ramadan schedule was active. Immutable after check-in.');
        });
    }

    public function down(): void
    {
        Schema::table('absences', function (Blueprint $table) {
            $table->dropColumn('is_ramadan');
        });
    }
};
