<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\ExamEnrollmentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ThemeController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Public Web Routes - DIYBIMA Blog + LMS
|--------------------------------------------------------------------------
|
| Catatan penting:
| - Login/registrasi ADMIN cuma di /admin (Filament) — gak ada link di navbar.
| - Login/registrasi USER ada di frontend (auth/student).
| - Frontend pakai bahasa prefix (?lang=id) ATAU detect otomatis (Lihat SetLocale middleware).
|
*/

// Theme toggle (light/dark mode)
Route::post('/theme/toggle', [ThemeController::class, 'toggle'])->name('theme.toggle');

// Switch locale (akan redirect kembali ke halaman asal)
Route::get('/locale/{locale}', [LocaleController::class, 'switch'])
    ->where('locale', 'id|en')
    ->name('locale.switch');

// Halaman beranda — bilingual (default redirect ke locale sesuai setting user)
Route::get('/', [HomeController::class, 'index'])->name('home');

// =====================================================================
// BLOGS (Publik, dengan komentar)
// =====================================================================
Route::prefix('blog')->name('blog.')->group(function () {
    Route::get('/',                  [BlogController::class, 'index'])->name('index');
    Route::get('/category/{slug}',   [BlogController::class, 'byCategory'])->name('category');
    Route::get('/search',            [BlogController::class, 'search'])->name('search');
    Route::get('/{slug}',            [BlogController::class, 'show'])->name('show');

    // Komentar (POST pakai auth:student)
    Route::middleware('auth:web')->group(function () {
        Route::post('/{slug}/comments', [CommentController::class, 'store'])->name('comments.store');
        Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
    });
});

// =====================================================================
// EXAMS (Halaman ujian, anti-screenshot)
// =====================================================================
Route::prefix('exams')->name('exam.')->group(function () {
    // List ujian (free preview, beberapa butuh login)
    Route::get('/', [ExamController::class, 'index'])->name('index');

    // Enrollment & start (wajib login)
    Route::middleware(['auth:web', \App\Http\Middleware\AntiScreenshot::class])->group(function () {
        Route::post('/{exam}/enroll',        [ExamEnrollmentController::class, 'enroll'])->name('enroll');
        Route::get('/{exam}/start',          [ExamController::class, 'start'])->name('start');
        Route::post('/attempts/{attempt}/save',  [ExamController::class, 'saveProgress'])->name('save');
        Route::post('/attempts/{attempt}/submit', [ExamController::class, 'submit'])->name('submit');
        Route::get('/attempts/{attempt}/result',  [ExamController::class, 'result'])->name('result');
    });

    Route::get('/{exam}', [ExamController::class, 'show'])->name('show');
});

// =====================================================================
// Auth untuk STUDENT (frontend, default role 'student')
// Login/registrasi admin TIDAK ada di sini. Admin login cuma di /admin
// =====================================================================
Route::middleware('guest:web')->group(function () {
    Route::get('login',     [\App\Http\Controllers\Auth\StudentLoginController::class, 'show'])->name('login');
    Route::post('login',    [\App\Http\Controllers\Auth\StudentLoginController::class, 'login']);
    Route::get('register',  [\App\Http\Controllers\Auth\StudentRegisterController::class, 'show'])->name('register');
    Route::post('register', [\App\Http\Controllers\Auth\StudentRegisterController::class, 'register']);
});

// Logout student
Route::middleware('auth:web')->post('logout', [\App\Http\Controllers\Auth\StudentLoginController::class, 'logout'])->name('logout');

// =====================================================================
// User Dashboard (student area) — lihat ujian yang diikutin, hasil, dll
// =====================================================================
Route::middleware('auth:web')->prefix('account')->name('account.')->group(function () {
    Route::get('/',         [\App\Http\Controllers\AccountController::class, 'index'])->name('index');
    Route::get('/attempts', [\App\Http\Controllers\AccountController::class, 'attempts'])->name('attempts');
});

// =====================================================================
// Halaman statis (privacy, terms, dll) - controlled via Settings
// =====================================================================
Route::view('/about',  'frontend.pages.about')->name('about');
Route::view('/contact', 'frontend.pages.contact')->name('contact');

// =====================================================================
// RSS Feed & Sitemap (bonus SEO)
// =====================================================================
Route::get('/feed.xml', [\App\Http\Controllers\BlogController::class, 'feed'])->name('feed');
Route::get('/sitemap.xml', [\App\Http\Controllers\BlogController::class, 'sitemap'])->name('sitemap');

// =====================================================================
// 404 fallback
// =====================================================================
Route::fallback(fn () => response()->view('frontend.errors.404', [], 404));
