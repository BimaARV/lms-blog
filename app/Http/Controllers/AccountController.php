<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $enrollments = $user->enrollments()->with('exam.category')->latest()->get();
        return view('frontend.account.index', compact('user', 'enrollments'));
    }

    public function attempts(): View
    {
        $attempts = Auth::user()->attempts()
            ->with('exam')
            ->latest()
            ->paginate(10);
        return view('frontend.account.attempts', compact('attempts'));
    }
}
