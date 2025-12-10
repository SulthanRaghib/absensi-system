<?php

namespace App\Models;

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
            'number' => (float) $setting->value,
            'json' => json_decode($setting->value, true),
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            default => $setting->value,
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
}
