<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class Settings
{
    public static function all(): array
    {
        return Cache::rememberForever('kendex.settings', fn () => Setting::pluck('value', 'key')->all());
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $all = static::all();

        if (array_key_exists($key, $all)) {
            $value = $all[$key];

            return is_numeric($value) ? $value + 0 : $value;
        }

        return config("kendex.defaults.$key", $default);
    }

    public static function set(string $key, mixed $value): void
    {
        Setting::updateOrCreate(['key' => $key], ['value' => (string) $value]);
        Cache::forget('kendex.settings');
    }
}
