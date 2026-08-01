<?php

namespace App\Providers;

use App\Services\SettingService;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Cegah error key length di MySQL < 5.7.7 / MariaDB lawas
        Schema::defaultStringLength(191);

        // Pagination pakai Tailwind (default Laravel 11 sudah bootstrap, kita override)
        Paginator::useTailwind();

        // Force HTTPS di production
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Hindari lazy loading N+1 (dev only, biar sadar)
        Model::preventLazyLoading(!app()->isProduction());

        // Apply dynamic settings ke runtime config (mail, app name, dll)
        $this->applySettings();

        // Share data global ke SEMUA frontend views
        $this->shareGlobals();

        // Blade directive: @admin / @endadmin
        Blade::if('admin', function () {
            return auth()->check() && auth()->user()->hasAnyRole(['super-admin', 'admin', 'editor']);
        });

        // Blade directive: @student
        Blade::if('student', function () {
            return auth()->check() && auth()->user()->hasRole('student');
        });

        // Register Filament global hooks
        $this->registerFilamentHooks();
    }

    protected function applySettings(): void
    {
        try {
            if (\Schema::hasTable('settings')) {
                SettingService::applyToConfig();
            }
        } catch (\Throwable $e) {
            // Gak ada DB / table belum migrate — biarin, gak apa-apa
        }
    }

    protected function shareGlobals(): void
    {
        View::composer('frontend.*', function ($view) {
            try {
                $siteName    = \App\Models\Setting::get('site_name', config('app.name'), 'general');
                $siteTagline = \App\Models\Setting::get('site_tagline', '', 'general');
                $socials     = \App\Models\Setting::get('links', [], 'social') ?: [];
            } catch (\Throwable $e) {
                $siteName    = config('app.name');
                $siteTagline = '';
                $socials     = [];
            }

            $view->with([
                'siteName'    => $siteName,
                'siteTagline' => $siteTagline,
                'socials'     => $socials,
                'currentLocale' => app()->getLocale(),
            ]);
        });
    }

    protected function registerFilamentHooks(): void
    {
        Filament::serving(function () {
            // Hook bisa diisi nanti (misal override theme, dll)
        });
    }
}
