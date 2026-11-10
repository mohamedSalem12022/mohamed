<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Retrieve a system setting value by key, with optional fallback default.
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        try {
            $setting = static::where('key', $key)->first();
            return ($setting !== null && $setting->value !== null) ? $setting->value : $default;
        } catch (\Throwable) {
            return $default;
        }
    }

    /**
     * Set or update a system setting by key.
     */
    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
