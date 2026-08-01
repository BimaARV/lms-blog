<?php

/*
 * Filament admin panel configuration.
 * URL admin: /admin (disembunyikan dari public navbar)
 * Login form khusus admin, gak nampil di frontend.
 */
return [
    'default' => [
        // Hidden dari public navbar + register page
        'path' => env('FILAMENT_ADMIN_PATH', 'admin'),

        // Logo kecil di login page admin
        'brand' => env('APP_NAME'),

        'middleware' => [
            'panelfriendly' => \App\Http\Middleware\ApplyDynamicSettings::class,
            'auth' => [
                \Filament\Http\Middleware\Authenticate::class,
            ],
            'locale' => [\App\Http\Middleware\SetLocale::class],
            'base' => [\Filament\Http\Middleware\BaseMiddleware::class],
        ],

        'login' => [
            'route' => 'login',
            'enabled' => true,
        ],
    ],
];
