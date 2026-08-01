<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $locale = app()->getLocale();

        // Postingan terbaru (5 terakhir)
        $latestPosts = Post::with(['author', 'category'])
            ->published()
            ->latest('published_at')
            ->limit(6)
            ->get();

        // Postingan popular (views_count tinggi)
        $popularPosts = Post::with(['author'])
            ->published()
            ->orderByDesc('views_count')
            ->limit(5)
            ->get();

        // Ujian yang lagi aktif
        $availableExams = Exam::with('category')
            ->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('available_from')->orWhere('available_from', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('available_until')->orWhere('available_until', '>=', now());
            })
            ->latest()
            ->limit(3)
            ->get();

        return view('frontend.home', compact('latestPosts', 'popularPosts', 'availableExams', 'locale'));
    }
}
