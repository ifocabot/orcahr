<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'group', 'label', 'description'];

    /** Get a setting value, cast to its declared type */
    public static function getOption(string $key, mixed $default = null): mixed
    {
        $setting = Cache::remember("setting:{$key}", 3600, fn() => static::where('key', $key)->first());

        if (!$setting)
            return $default;

        return match ($setting->type) {
            'integer' => (int) $setting->value,
            'decimal' => (float) $setting->value,
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($setting->value, true),
            default => $setting->value,
        };
    }

    /** Update a setting and bust cache */
    public static function set(string $key, mixed $value): void
    {
        static::where('key', $key)->update(['value' => is_array($value) ? json_encode($value) : (string) $value]);
        Cache::forget("setting:{$key}");
    }
}
