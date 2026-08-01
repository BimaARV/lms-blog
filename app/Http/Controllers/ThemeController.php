<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ThemeController extends Controller
{
    public function toggle(Request $request): JsonResponse
    {
        $theme = $request->input('theme') === 'dark' ? 'dark' : 'light';
        cookie()->queue(cookie()->forever('app_theme', $theme));

        return response()->json(['ok' => true, 'theme' => $theme]);
    }
}
