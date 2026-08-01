<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id', 'user_id', 'question_snapshot', 'answers',
        'score', 'max_score', 'started_at', 'submitted_at', 'expires_at',
        'status', 'ip_address', 'user_agent',
    ];

    protected $casts = [
        'question_snapshot' => 'array',
        'answers'           => 'array',
        'started_at'        => 'datetime',
        'submitted_at'      => 'datetime',
        'expires_at'        => 'datetime',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && now()->gt($this->expires_at);
    }

    public function timeRemaining(): int
    {
        if (!$this->expires_at) {
            return 0;
        }
        return max(0, now()->diffInSeconds($this->expires_at, false));
    }

    public function percentScore(): float
    {
        if (!$this->max_score || $this->max_score == 0) {
            return 0.0;
        }
        return round(($this->score / $this->max_score) * 100, 2);
    }

    public function isPassed(): bool
    {
        return $this->percentScore() >= ($this->exam?->passing_score ?? 70);
    }
}
