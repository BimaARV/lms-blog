<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamEnrollment;
use App\Models\Question;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\ExamScoreMailable;
use App\Services\WhatsAppService;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;

class ExamController extends Controller
{
    protected $waService;

    public function __construct(WhatsAppService $waService)
    {
        $this->waService = $waService;
    }

    public function index(Request $request): View
    {
        $exams = Exam::with('category')
            ->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('available_from')->orWhere('available_from', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('available_until')->orWhere('available_until', '>=', now());
            })
            ->latest()
            ->paginate(12);

        return view('frontend.exam.index', compact('exams'));
    }

    public function show(Exam $exam): View
    {
        $exam->load(['category']);
        $isEnrolled = false;
        if (Auth::check()) {
            $isEnrolled = ExamEnrollment::where('exam_id', $exam->id)
                ->where('user_id', Auth::id())
                ->exists();
        }
        return view('frontend.exam.show', compact('exam', 'isEnrolled'));
    }

    public function start(Request $request, Exam $exam)
    {
        $user = Auth::user();

        if (!$exam->isAvailable()) {
            return redirect()->back()->with('error', __('exam.unavailable'));
        }

        if ($exam->require_enrollment) {
            $enrollment = ExamEnrollment::where('exam_id', $exam->id)
                ->where('user_id', $user->id)
                ->first();

            if (!$enrollment) {
                return redirect()->route('exam.show', $exam)
                    ->with('error', __('exam.enroll_required'));
            }

            if ($exam->max_attempts > 0 && $enrollment->attempts_used >= $exam->max_attempts) {
                return redirect()->back()->with('error', __('exam.no_attempts_left'));
            }
        }

        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('user_id', $user->id)
            ->where('status', 'in_progress')
            ->first();

        if (!$attempt) {
            $questions = $exam->questions()->get();
            if ($exam->shuffle_questions) {
                $questions = $questions->shuffle();
            }

            $maxScore = $questions->sum('score');
            $expiresAt = now()->addMinutes($exam->duration_minutes);

            $attempt = ExamAttempt::create([
                'exam_id'            => $exam->id,
                'user_id'            => $user->id,
                'question_snapshot'  => ['questions' => $questions->map->toArray()],
                'answers'            => [],
                'max_score'          => $maxScore,
                'started_at'         => now(),
                'expires_at'         => $expiresAt,
                'status'             => 'in_progress',
                'ip_address'         => $request->ip(),
                'user_agent'         => substr((string) $request->userAgent(), 0, 250),
            ]);

            $enrollment?->increment('attempts_used');
        } else {
            if ($attempt->isExpired()) {
                $this->autoSubmit($attempt);
                return redirect()->route('exam.result', $attempt)
                    ->with('info', __('exam.time_up'));
            }
        }

        return view('frontend.exam.take', compact('exam', 'attempt'));
    }

    public function saveProgress(Request $request, ExamAttempt $attempt)
    {
        abort_unless($attempt->user_id === Auth::id(), 403);
        abort_unless($attempt->status === 'in_progress', 410);

        $validated = $request->validate([
            'answers' => 'required|array',
        ]);

        $snapshotIds = collect($attempt->question_snapshot['questions'] ?? [])->pluck('id')->toArray();
        foreach (array_keys($validated['answers']) as $ansId) {
            if (!in_array($ansId, $snapshotIds)) {
                return response()->json(['error' => 'Invalid question ID detected!'], 422);
            }
        }

        if ($attempt->isExpired()) {
            $attempt->answers = $validated['answers'];
            $this->autoSubmit($attempt);
            return response()->json(['expired' => true, 'redirect' => route('exam.result', $attempt)]);
        }

        $attempt->update(['answers' => $validated['answers']]);

        return response()->json([
            'saved' => true,
            'time_remaining' => $attempt->timeRemaining(),
        ]);
    }

    public function submit(Request $request, ExamAttempt $attempt)
    {
        abort_unless($attempt->user_id === Auth::id(), 403);
        abort_unless($attempt->status === 'in_progress', 410);

        $validated = $request->validate([
            'answers' => 'sometimes|array',
        ]);

        if (isset($validated['answers'])) {
            $snapshotIds = collect($attempt->question_snapshot['questions'] ?? [])->pluck('id')->toArray();
            foreach (array_keys($validated['answers']) as $ansId) {
                if (!in_array($ansId, $snapshotIds)) {
                    return back()->with('error', 'Deteksi kecurangan: ID Soal tidak valid!');
                }
            }
            $attempt->answers = $validated['answers'];
        }

        try {
            $this->gradeAttempt($attempt);
            
            // TRIGGER PENGIRIMAN NOTIFIKASI
            $this->sendNotifications($attempt);

        } catch (Exception $e) {
            Log::error('Grading error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menghitung skor.');
        }

        return redirect()->route('exam.result', $attempt);
    }

    protected function gradeAttempt(ExamAttempt $attempt): void
    {
        $answers = $attempt->answers ?? [];
        $score = 0;
        $essayPending = 0;

        foreach ($attempt->question_snapshot['questions'] ?? [] as $q) {
            $questionId = $q['id'];
            $userAns = $answers[$questionId] ?? null;

            if ($q['type'] === 'essay') {
                if (!empty($userAns['text'] ?? '')) {
                    $essayPending++;
                }
                continue;
            }

            $questionModel = Question::find($questionId);
            if (!$questionModel) continue;

            $questionModel->fill([
                'type'             => $q['type'],
                'options'          => $q['options'] ?? null,
                'correct_answers'  => $q['correct_answers'] ?? null,
                'score'            => $q['score'] ?? 1,
            ]);

            if ($questionModel->isCorrect((array) $userAns['selected'] ?? [])) {
                $score += $questionModel->score;
            }
        }

        $attempt->score = $score;
        $attempt->submitted_at = now();
        $attempt->status = $essayPending > 0 ? 'submitted' : 'graded';
        $attempt->save();
    }

    protected function sendNotifications(ExamAttempt $attempt)
    {
        $user = $attempt->user;

        try {
            // 1. Generate PDF
            $pdf = Pdf::loadView('frontend.exam.pdf-result', compact('attempt'));
            $pdfContent = $pdf->output();

            // 2. Kirim Email dengan Attachment
            Mail::to($user->email)->send(new ExamScoreMailable($attempt))
                ->attachData($pdfContent, "Skor_{$attempt->exam->title}.pdf", [
                    'mime' => 'application/pdf',
                ]);

            // 3. Kirim WhatsApp (Kalo nomor ada)
            if ($user->phone) {
                $this->waService->sendScoreNotification($user->phone, $attempt);
            }

        } catch (Exception $e) {
            Log::error('Notification Error: ' . $e->getMessage());
        }
    }

    public function updateEssayScore(Request $request, ExamAttempt $attempt)
    {
        abort_unless(Auth::user()->hasRole('admin'), 403);

        $validated = $request->validate([
            'scores' => 'required|array',
        ]);

        DB::beginTransaction();
        try {
            $currentScore = 0;
            $answers = $attempt->answers ?? [];

            foreach ($attempt->question_snapshot['questions'] ?? [] as $q) {
                $questionId = $q['id'];
                
                if ($q['type'] === 'essay') {
                    $currentScore += $validated['scores'][$questionId] ?? 0;
                } else {
                    $questionModel = Question::find($questionId);
                    if ($questionModel && $questionModel->isCorrect((array) $answers[$questionId]['selected'] ?? [])) {
                        $currentScore += $questionModel->score;
                    }
                }
            }

            $attempt->update([
                'score' => $currentScore,
                'status' => 'graded'
            ]);

            // Kirim ulang notifikasi setelah admin update nilai
            $this->sendNotifications($attempt);

            DB::commit();
            return response()->json(['message' => 'Skor berhasil diperbarui!']);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal update skor: ' . $e->getMessage()], 500);
        }
    }

    protected function autoSubmit(ExamAttempt $attempt): void
    {
        try {
            $this->gradeAttempt($attempt);
            $this->sendNotifications($attempt);
        } catch (Exception $e) {
            Log::error('Auto-submit error: ' . $e->getMessage());
        }
    }

    public function result(ExamAttempt $attempt): View
    {
        abort_unless($attempt->user_id === Auth::id(), 403);

        if ($attempt->status === 'in_progress') {
            return redirect()->route('exam.show', $attempt->exam);
        }

        return view('frontend.exam.result', compact('attempt'));
    }
}
