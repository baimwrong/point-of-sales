<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'group'];

    protected static $cache = [];

    public static function get(string $key, $default = null)
    {
        if (array_key_exists($key, static::$cache)) {
            return static::$cache[$key] ?? $default;
        }

        $value = Cache::remember("setting:{$key}", now()->addHours(24), function () use ($key) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : null;
        });

        static::$cache[$key] = $value;
        return $value !== null ? $value : $default;
    }

    public static function set(string $key, $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
        static::$cache[$key] = $value;
        Cache::forget("setting:{$key}");
    }

    public static function getGroup(string $group): array
    {
        return static::where('group', $group)->pluck('value', 'key')->toArray();
    }

    public static function clearCache(): void
    {
        try {
            $keys = static::pluck('key');
            foreach ($keys as $k) {
                Cache::forget("setting:{$k}");
            }
        } catch (\Throwable $e) {
            // Ignore during setup/migrations
        }
        foreach (array_keys(static::$cache) as $k) {
            Cache::forget("setting:{$k}");
        }
        static::$cache = [];
    }
}
