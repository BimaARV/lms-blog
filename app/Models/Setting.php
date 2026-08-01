<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Settings disimpan di DB, BUKAN .env.
 * Di-cache Redis biar gak query tiap kali dipanggil.
 */
class Setting extends Model
{
    protected $fillable = [
        'group', 'key', 'value', 'type', 'description',
    ];

    public static function groups(): array
    {
        return [
            'general'    => 'General',
            'smtp'       => 'Mail / SMTP',
            'baileys'    => 'WhatsApp Gateway (Baileys)',
            'telegram'   => 'Telegram Bot',
            'social'     => 'Social Media',
            'seo'        => 'SEO Defaults',
            'appearance' => 'Appearance',
            'exam'       => 'Exam Settings',
            'maintenance'=> 'Maintenance',
        ];
    }

    /* =======================================================
       STATIC HELPERS (cached)
       ======================================================= */

    private static function cacheKey(string $group, string $key): string
    {
        return "setting:{$group}:{$key}";
    }

    public static function get(string $key, mixed $default = null, ?string $group = null): mixed
    {
        $group = $group ?? 'general';

        return Cache::rememberForever(self::cacheKey($group, $key), function () use ($group, $key, $default) {
            $row = self::where('group', $group)->where('key', $key)->first();
            if (!$row) {
                return $default;
            }
            return self::castValue($row->value, $row->type);
        });
    }

    public static function set(string $key, mixed $value, string $type = 'string', ?string $group = null, ?string $description = null): void
    {
        $group = $group ?? 'general';

        $storedValue = match ($type) {
            'boolean' => $value ? '1' : '0',
            'json'    => json_encode($value, JSON_UNESCAPED_UNICODE),
            default   => (string) $value,
        };

        self::updateOrCreate(
            ['group' => $group, 'key' => $key],
            ['value' => $storedValue, 'type' => $type, 'description' => $description]
        );

        Cache::forget(self::cacheKey($group, $key));
    }

    public static function castValue(?string $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }
        return match ($type) {
            'boolean' => (bool) $value,
            'integer' => (int) $value,
            'json'    => json_decode($value, true),
            default   => $value,
        };
    }

    public static function clearCache(): void
    {
        Cache::flush();
    }
}
