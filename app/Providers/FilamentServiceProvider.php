<?php

namespace App\Providers;

use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;

class FilamentServiceProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path(env('FILAMENT_ADMIN_PATH', 'admin'))
            ->brandName(config('app.name'))
            ->login()
            ->authGuard('web')
            ->colors([
                'primary' => Color::hex('#00a8f4'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
                \App\Filament\Pages\Settings::class,
                \App\Filament\Pages\BaileysControl::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                \App\Filament\Widgets\StatsOverview::class,
                \App\Filament\Widgets\LatestPosts::class,
                \App\Filament\Widgets\LatestAttempts::class,
            ])
            ->navigationGroups([
                NavigationGroup::label('Blog')->icon('heroicon-o-newspaper'),
                NavigationGroup::label('Ujian / LMS')->icon('heroicon-o-academic-cap'),
                NavigationGroup::label('Interaksi')->icon('heroicon-o-chat-bubble-left-right'),
                NavigationGroup::label('System')->icon('heroicon-o-cog-6-tooth')->collapsible(),
            ])
            ->middleware([
                \App\Http\Middleware\ApplyDynamicSettings::class,
            ])
            ->authMiddleware([
                \Filament\Http\Middleware\Authenticate::class,
            ]);
    }
}
