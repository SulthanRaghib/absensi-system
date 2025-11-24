<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, number, json
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insert default office location
        DB::table('settings')->insert([
            [
                'key' => 'office_latitude',
                'value' => '-6.1787051',
                'type' => 'number',
                'description' => 'Latitude BAPETEN',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'office_longitude',
                'value' => '106.8109582',
                'type' => 'number',
                'description' => 'Longitude BAPETEN',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'office_radius',
                'value' => '100',
                'type' => 'number',
                'description' => 'Radius absensi dalam meter',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
