<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $table = 'settings';

    protected $fillable = [
        'key',
        'value',
        'group',
    ];

    /**
     * Valori di default di sistema se non presenti a database.
     */
    public const DEFAULTS = [
        'media_max_image_mb' => 8,
        'media_max_video_mb' => 50,
        'media_auto_compress' => true,
        'media_max_dimension_px' => 1920,
        'media_quality_percent' => 82,
        'media_convert_webp' => true,
    ];

    /**
     * Recupera il valore di una configurazione per chiave.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::query()->where('key', $key)->first();

        if ($setting !== null && $setting->value !== null) {
            return $setting->value;
        }

        if ($default !== null) {
            return $default;
        }

        return static::DEFAULTS[$key] ?? null;
    }

    /**
     * Salva o aggiorna il valore di una configurazione.
     */
    public static function set(string $key, mixed $value, string $group = 'general'): void
    {
        $stringValue = is_bool($value)
            ? ($value ? '1' : '0')
            : (is_array($value) ? json_encode($value) : (string) $value);

        static::query()->updateOrCreate(
            ['key' => $key],
            [
                'value' => $stringValue,
                'group' => $group,
            ]
        );
    }

    /**
     * Recupera un valore numerico intero.
     */
    public static function getInt(string $key, int $default = 0): int
    {
        $hasDefault = func_num_args() >= 2;
        $fallback = $hasDefault ? $default : (static::DEFAULTS[$key] ?? $default);
        $value = static::get($key, $fallback);

        return (int) $value;
    }

    /**
     * Recupera un valore booleano.
     */
    public static function getBool(string $key, bool $default = false): bool
    {
        $hasDefault = func_num_args() >= 2;
        $fallback = $hasDefault ? $default : (static::DEFAULTS[$key] ?? $default);
        $value = static::get($key, $fallback);

        if (is_bool($value)) {
            return $value;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
