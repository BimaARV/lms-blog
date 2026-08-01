<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Config;

/**
 * Service untuk sinkronkan setting DB ke runtime config Laravel.
 * Dipanggil di ServiceProvider, jadi sekali semua setting udah siap.
 */
class SettingService
{
    /**
     * Apply settings ke runtime Config (untuk SMTP, mail, dll).
     */
    public static function applyToConfig(): void
    {
        // === SMTP ===
        $smtpHost = Setting::get('host', null, 'smtp');
        if ($smtpHost) {
            Config::set('mail.mailers.smtp.host',       $smtpHost);
            Config::set('mail.mailers.smtp.port',       (int) Setting::get('port', 587, 'smtp'));
            Config::set('mail.mailers.smtp.username',   Setting::get('username', null, 'smtp'));
            Config::set('mail.mailers.smtp.password',   Setting::get('password', null, 'smtp'));
            Config::set('mail.mailers.smtp.encryption', Setting::get('encryption', 'tls', 'smtp'));

            $fromAddr = Setting::get('from_address', null, 'smtp');
            $fromName = Setting::get('from_name', null, 'smtp');
            if ($fromAddr) {
                Config::set('mail.from.address', $fromAddr);
                Config::set('mail.from.name',    $fromName ?: config('app.name'));
            }
        }

        // === Mail default driver ===
        $driver = Setting::get('mail_driver', config('mail.default'), 'smtp');
        Config::set('mail.default', $driver);

        // === App name dari settings ===
        $siteName = Setting::get('site_name', null, 'general');
        if ($siteName) {
            Config::set('app.name', $siteName);
        }

        // === Site URL ===
        $siteUrl = Setting::get('site_url', null, 'general');
        if ($siteUrl) {
            Config::set('app.url', rtrim($siteUrl, '/'));
        }

        // === Maintenance mode via flag ===
        $maintenanceOn = Setting::get('enabled', false, 'maintenance');
        if ($maintenanceOn) {
            // Implementasi maintenance mode kalo perlu
            // Bisa pake Artisan::call('down', ...) di console
        }
    }

    /**
     * Helper buat ambil setting nested (sesuai grouping).
     */
    public static function smtp(): array
    {
        return [
            'host'       => Setting::get('host', env('MAIL_HOST')),
            'port'       => Setting::get('port', env('MAIL_PORT', 587)),
            'username'   => Setting::get('username', env('MAIL_USERNAME')),
            'password'   => Setting::get('password', env('MAIL_PASSWORD')),
            'encryption' => Setting::get('encryption', env('MAIL_ENCRYPTION', 'tls')),
            'from'       => Setting::get('from_address', env('MAIL_FROM_ADDRESS')),
            'from_name'  => Setting::get('from_name', env('MAIL_FROM_NAME')),
        ];
    }

    public static function general(): array
    {
        return [
            'site_name'    => Setting::get('site_name', config('app.name'), 'general'),
            'site_tagline' => Setting::get('site_tagline', '', 'general'),
            'site_url'     => Setting::get('site_url', config('app.url'), 'general'),
            'locale'       => Setting::get('locale', 'id', 'general'),
        ];
    }
}
