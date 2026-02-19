<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    /**
     * Check if radius check is enabled
     */
    public static function isRadiusEnabled(): bool
    {
        return self::get('radius_enabled', true);
    }

    /**
     * Get setting value by key
     */
    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();

        if (!$setting) {
            return $default;
        }

        return match ($setting->type) {
            'number'  => (float) $setting->value,
            'json'    => json_decode($setting->value, true),
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'date'    => $setting->value ? Carbon::parse($setting->value) : null,
            'time'    => $setting->value ? substr((string) $setting->value, 0, 5) : null,
            default   => $setting->value,
        };
    }

    /**
     * Set setting value
     */
    public static function set(string $key, $value, string $type = 'string'): void
    {
        $valueToStore = is_array($value) ? json_encode($value) : $value;

        static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $valueToStore,
                'type' => $type,
            ]
        );
    }

    /**
     * Get office coordinates
     */
    public static function getOfficeLocation(): array
    {
        return [
            'latitude' => static::get('office_latitude', -6.163836),
            'longitude' => static::get('office_longitude', 106.8189579),
            'radius' => static::get('office_radius', 100),
        ];
    }

    /**
     * Get all Ramadan schedule settings.
     * Returns an array with start_date, end_date, jam_masuk, jam_pulang (all nullable).
     */
    public static function getRamadanSettings(): array
    {
        return [
            'start_date'  => static::get('ramadan_start_date'),
            'end_date'    => static::get('ramadan_end_date'),
            'jam_masuk'   => static::get('ramadan_jam_masuk'),
            'jam_pulang'  => static::get('ramadan_jam_pulang'),
        ];
    }

    /**
     * Get the configurable default (non-Ramadan) work schedule.
     *
     * Values come from the settings table so admin can change them from the UI.
     * Falls back to hardcoded defaults if the rows are missing (fresh install).
     */
    public static function getDefaultSchedule(): array
    {
        return [
            'jam_masuk'         => static::get('default_jam_masuk')         ?? '07:30',
            'jam_pulang'        => static::get('default_jam_pulang')         ?? '16:00',
            'jam_pulang_jumat'  => static::get('default_jam_pulang_jumat')  ?? '16:30',
        ];
    }

    /**
     * Persist the default (non-Ramadan) schedule settings at once.
     */
    public static function saveDefaultSchedule(
        string $jamMasuk,
        string $jamPulang,
        string $jamPulangJumat
    ): void {
        static::set('default_jam_masuk',        $jamMasuk,       'time');
        static::set('default_jam_pulang',        $jamPulang,      'time');
        static::set('default_jam_pulang_jumat',  $jamPulangJumat, 'time');
    }

    /**
     * Persist all Ramadan schedule settings at once.
     */
    public static function saveRamadanSettings(
        ?string $startDate,
        ?string $endDate,
        ?string $jamMasuk,
        ?string $jamPulang
    ): void {
        static::set('ramadan_start_date', $startDate, 'date');
        static::set('ramadan_end_date',   $endDate,   'date');
        static::set('ramadan_jam_masuk',  $jamMasuk,  'time');
        static::set('ramadan_jam_pulang', $jamPulang, 'time');
    }
}
