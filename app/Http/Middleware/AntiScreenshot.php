<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Anti-screening untuk halaman ujian.
 * Header HTTP yang ditambah:
 *  - X-Frame-Options: DENY (gak bisa di iframe)
 *  - Cache-Control: no-store, no-cache, must-revalidate
 *  - Permissions-Policy: clipboard-read=(self), clipboard-write=(self) (browser-side)
 *
 * Sisanya (disable right-click, disable text selection, watermark) ada di Blade component.
 */
class AntiScreenshot
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'no-referrer');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        $response->headers->set(
            'Permissions-Policy',
            'camera=(), microphone=(), geolocation=(), payment=(), usb=()'
        );

        return $response;
    }
}
