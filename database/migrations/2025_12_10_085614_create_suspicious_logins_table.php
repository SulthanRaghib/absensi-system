<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('suspicious_logins', function (Blueprint $table) {
            $table->id();
            $table->string('attempted_email');
            $table->string('ip_address', 45);
            $table->foreignId('previous_user_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('blocked_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suspicious_logins');
    }
};
