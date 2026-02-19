<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Absence extends Model
{
    protected $fillable = [
        'user_id',
        'tanggal',
        'jam_masuk',
        'jam_pulang',
        'schedule_jam_masuk',   // HH:MM threshold active at the time of check-in (immutable)
        'lat_masuk',
        'lng_masuk',
        'lat_pulang',
        'lng_pulang',
        'distance_masuk',
        'distance_pulang',
        'device_info',
        'capture_image',
        'risk_level',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jam_masuk' => 'datetime',
        'jam_pulang' => 'datetime',
        'lat_masuk' => 'decimal:8',
        'lng_masuk' => 'decimal:8',
        'lat_pulang' => 'decimal:8',
        'lng_pulang' => 'decimal:8',
        'distance_masuk' => 'decimal:2',
        'distance_pulang' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if user already checked in today
     */
    public static function hasCheckedInToday(int $userId): bool
    {
        return static::where('user_id', $userId)
            ->whereDate('tanggal', today())
            ->whereNotNull('jam_masuk')
            ->exists();
    }

    /**
     * Check if user already checked out today
     */
    public static function hasCheckedOutToday(int $userId): bool
    {
        return static::where('user_id', $userId)
            ->whereDate('tanggal', today())
            ->whereNotNull('jam_pulang')
            ->exists();
    }

    /**
     * Get today's absence for user
     */
    public static function getTodayAbsence(int $userId): ?Absence
    {
        return static::where('user_id', $userId)
            ->whereDate('tanggal', today())
            ->first();
    }

    /**
     * Format distance for display
     */
    public function getFormattedDistanceMasukAttribute(): string
    {
        return $this->distance_masuk ? number_format($this->distance_masuk, 2) . ' m' : '-';
    }

    public function getFormattedDistancePulangAttribute(): string
    {
        return $this->distance_pulang ? number_format($this->distance_pulang, 2) . ' m' : '-';
    }

    /**
     * Check if distance is valid
     */
    public function isValidDistance(float $distance): bool
    {
        $maxRadius = Setting::get('office_radius', 100);
        return $distance <= $maxRadius;
    }
}
