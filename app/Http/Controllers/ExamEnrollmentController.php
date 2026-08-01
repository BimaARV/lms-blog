<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamEnrollment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExamEnrollmentController extends Controller
{
    public function enroll(Request $request, Exam $exam): RedirectResponse
    {
        $user = Auth::user();

        if (!$exam->isAvailable()) {
            return back()->with('error', __('exam.unavailable'));
        }

        if (!$exam->require_enrollment) {
            return back()->with('info', __('exam.no_enroll_needed'));
        }

        // Cek apakah sudah pernah enroll & attempt masih ada
        $existing = ExamEnrollment::where('exam_id', $exam->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            return back()->with('info', __('exam.already_enrolled'));
        }

        ExamEnrollment::create([
            'exam_id'      => $exam->id,
            'user_id'      => $user->id,
            'attempts_used' => 0,
            'enrolled_at'  => now(),
        ]);

        return back()->with('success', __('exam.enrolled_success'));
    }
}
