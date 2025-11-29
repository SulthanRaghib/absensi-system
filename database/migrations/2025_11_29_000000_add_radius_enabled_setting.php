<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ensure the setting doesn't already exist
        if (!DB::table('settings')->where('key', 'radius_enabled')->exists()) {
            DB::table('settings')->insert([
                'key' => 'radius_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Aktifkan/Nonaktifkan pengecekan radius lokasi (Global)',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->where('key', 'radius_enabled')->delete();
    }
};
