<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StudentLoginController extends Controller
{
    public function show(): View
    {
        return view('frontend.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $throttleKey = 'student-login:' . $request->ip() . ':' . strtolower((string) $request->input('email'));

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => [__('Terlalu banyak percobaan login. Coba lagi dalam :seconds detik.', ['seconds' => $seconds])],
            ]);
        }

        $validated = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string|min:6',
            'g-recaptcha-response' => 'sometimes|nullable|string',
            'captcha'  => 'sometimes|nullable|string',
        ]);

        $user = \App\Models\User::where('email', $validated['email'])->first();

        if (!$user) {
            RateLimiter::hit($throttleKey);
            throw ValidationException::withMessages(['email' => __('Email atau password salah.')]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages(['email' => __('Akun non-aktif. Hubungi admin.')]);
        }

        // Cegah ADMIN login dari form student
        if ($user->hasAnyRole(['super-admin', 'admin', 'editor'])) {
            throw ValidationException::withMessages([
                'email' => __('Akun admin gak bisa login dari sini. Pakai halaman /admin ya.'),
            ]);
        }

        // Validasi captcha kalo disediain aja (mews/captcha)
        if (function_exists('captcha') && isset($validated['captcha'])) {
            $captchaValidator = captcha()->validate($validated['captcha'], $request->input('captcha_key') ?? '');
            if (!$captchaValidator) {
                throw ValidationException::withMessages(['captcha' => __('Captcha salah.')]);
            }
        }

        if (!Auth::attempt(['email' => $validated['email'], 'password' => $validated['password']], true)) {
            RateLimiter::hit($throttleKey);
            throw ValidationException::withMessages(['email' => __('Email atau password salah.')]);
        }

        RateLimiter::clear($throttleKey);

        $request->session()->regenerate();
        $user->locale = app()->getLocale();
        $user->save();

        return redirect()->intended(route('account.index'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
