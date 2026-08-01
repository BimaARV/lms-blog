<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Multi-bahasa via URL prefix (?lang=xx) ATAU detect dari browser ATAU dari user preference.
 * Available: id, en
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = ['id', 'en'];

        $locale = $request->segment(1);
        if (in_array($locale, $supported)) {
            app()->setLocale($locale);
            session(['app_locale' => $locale]);
            return $next($request);
        }

        $sessionLocale = session('app_locale');
        if (in_array($sessionLocale, $supported)) {
            app()->setLocale($sessionLocale);
            return $next($request);
        }

        $user = $request->user();
        if ($user && in_array($user->locale ?? null, $supported)) {
            app()->setLocale($user->locale);
            return $next($request);
        }

        $browserLocale = substr($request->header('Accept-Language') ?? 'id', 0, 2);
        app()->setLocale(in_array($browserLocale, $supported) ? $browserLocale : config('app.locale'));

        return $next($request);
    }
}
