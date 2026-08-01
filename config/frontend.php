<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Frontend Configuration
    |--------------------------------------------------------------------------
    |
    | Semua setting untuk tampilan publik.
    | Beberapa bisa di-override via admin panel (Settings > Appearance).
    |
    */

    'name'        => env('APP_NAME', 'DIYBIMA Blog'),
    'tagline'     => 'Belajar, sharing, & ujian bareng-bareng.',

    // Theme: 'light', 'dark', atau 'auto'
    'default_theme' => 'light',

    // Tipe konten yang aktif
    'features' => [
        'blog'           => true,
        'exam'           => true,
        'comments'       => true,
        'subscription'   => false,
        'rss'            => true,
        'sitemap'        => true,
        'captcha'        => true,
        'multi_language' => true,
    ],

    // Pengaturan anti-cheat ujian
    'exam_security' => [
        'disable_right_click'   => true,
        'disable_text_selection' => true,
        'block_print'           => true,
        'watermark'             => true,
        'block_devtools'        => false, // false = UX masih oke
        'fullscreen_required'   => true,
    ],

    // Pagination
    'pagination' => [
        'blog_per_page' => 9,
        'exam_per_page' => 12,
    ],

    // Social (override via Settings > Social)
    'social' => [
        'github'   => '',
        'twitter'  => '',
        'facebook' => '',
        'instagram' => '',
        'youtube'  => '',
        'tiktok'   => '',
    ],
];
