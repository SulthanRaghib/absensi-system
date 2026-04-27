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
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('reference_type')->nullable()->after('type')->comment('permission or attendance_correction');
            $table->unsignedBigInteger('reference_id')->nullable()->after('reference_type')->comment('ID dari Permission atau AttendanceCorrection');
            $table->boolean('is_cleared')->default(false)->after('read_at')->comment('User sudah clear notifikasi dari sidebar?');
            $table->enum('status', ['pending', 'approved', 'rejected', 'archived'])->default('pending')->after('is_cleared')->comment('Status: pending=menunggu approval, approved=sudah disetujui, rejected=ditolak, archived=cleared');
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['reference_type', 'reference_id']);
            $table->dropColumn(['reference_type', 'reference_id', 'is_cleared', 'status']);
        });
    }
};
