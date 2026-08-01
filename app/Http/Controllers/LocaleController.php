<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function switch(Request $request, string $locale): RedirectResponse
    {
        abort_unless(in_array($locale, ['id', 'en']), 404);

        // Simpan di session
        session(['app_locale' => $locale]);

        // Kalo login, update db user juga
        if ($user = $request->user()) {
            $user->locale = $locale;
            $user->save();
        }

        return redirect()->back()
            ->withCookie(cookie()->forever('app_locale', $locale));
    }
}
