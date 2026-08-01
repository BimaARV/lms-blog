<?php

namespace App\Http\Middleware;

use App\Services\SettingService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Apply settings DB ke runtime config SEBELUM request diproses.
 * Kalo ada edit setting SMTP via admin, gak perlu restart php-fpm.
 */
class ApplyDynamicSettings
{
    public function handle(Request $request, Closure $next): Response
    {
        SettingService::applyToConfig();
        return $next($request);
    }
}
