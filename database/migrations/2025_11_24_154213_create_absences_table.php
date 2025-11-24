<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('tanggal');
            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();
            $table->decimal('lat_masuk', 10, 8)->nullable();
            $table->decimal('lng_masuk', 11, 8)->nullable();
            $table->decimal('lat_pulang', 10, 8)->nullable();
            $table->decimal('lng_pulang', 11, 8)->nullable();
            $table->decimal('distance_masuk', 8, 2)->nullable(); // in meters
            $table->decimal('distance_pulang', 8, 2)->nullable(); // in meters
            $table->string('device_info')->nullable();
            $table->timestamps();

            // Composite unique: satu user hanya bisa absen sekali per hari
            $table->unique(['user_id', 'tanggal']);
            $table->index(['tanggal', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absences');
    }
};
