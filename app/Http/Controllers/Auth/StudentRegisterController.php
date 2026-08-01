<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StudentRegisterController extends Controller
{
    public function show(): View
    {
        return view('frontend.auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $throttleKey = 'student-register:' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => [__('Terlalu banyak percobaan registrasi. Coba :seconds detik lagi.', ['seconds' => $seconds])],
            ]);
        }

        $validated = $request->validate([
            'name'                  => 'required|string|min:3|max:100',
            'email'                 => 'required|email|max:191|unique:users,email',
            'phone'                 => ['required', 'string', 'min:8', 'max:20', Rule::unique('users', 'phone')],
            'password'              => 'required|string|min:8|confirmed|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/',
            'g-recaptcha-response'  => 'sometimes|nullable|string',
            'captcha'               => 'sometimes|nullable|string',
            'terms'                 => 'accepted',
        ], [
            'password.regex'         => 'Password harus ada huruf besar, kecil, dan angka.',
            'phone.unique'           => 'No HP udah kepake.',
            'email.unique'           => 'Email udah terdaftar.',
            'terms.accepted'         => 'Loe harus setuju dengan Terms of Service.',
        ]);

        // Validasi captcha
        if (function_exists('captcha') && isset($validated['captcha'])) {
            if (!captcha()->validate($validated['captcha'], $request->input('captcha_key') ?? '')) {
                throw ValidationException::withMessages(['captcha' => 'Captcha salah, Bos.']);
            }
        }

        $user = User::create([
            'name'     => strip_tags($validated['name']),
            'email'    => strtolower($validated['email']),
            'phone'    => preg_replace('/[^0-9+]/', '', $validated['phone']),
            'password' => Hash::make($validated['password']),
            'locale'   => app()->getLocale(),
            'is_active' => true,
        ]);

        // Assign role 'student' (default user)
        $user->assignRole('student');

        RateLimiter::hit($throttleKey);

        // Auto login setelah registrasi
        auth()->login($user);

        return redirect()->route('account.index')->with('success', __('Selamat datang, Bos! Akun DIYBIMA lo udah aktif.'));
    }
}
