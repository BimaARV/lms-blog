<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Exam extends Model
{
    use HasFactory;
    use HasTranslations;

    public array $translatable = ['title', 'description'];

    protected $fillable = [
        'category_id', 'title', 'description',
        'duration_minutes', 'passing_score', 'max_attempts',
        'shuffle_questions', 'shuffle_answers',
        'show_result_immediately', 'require_enrollment',
        'available_from', 'available_until', 'status',
    ];

    protected $casts = [
        'available_from' => 'datetime',
        'available_until' => 'datetime',
        'shuffle_questions' => 'boolean',
        'shuffle_answers' => 'boolean',
        'show_result_immediately' => 'boolean',
        'require_enrollment' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('sort_order');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(ExamEnrollment::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function isAvailable(): bool
    {
        if ($this->status !== 'published') {
            return false;
        }
        if ($this->available_from && now()->lt($this->available_from)) {
            return false;
        }
        if ($this->available_until && now()->gt($this->available_until)) {
            return false;
        }
        return true;
    }

    public function canEnroll(User $user): bool
    {
        $enrollment = $this->enrollments()->where('user_id', $user->id)->first();

        if (!$enrollment) {
            return true;
        }

        if ($this->max_attempts === 0) {
            return true;
        }

        return $enrollment->attempts_used < $this->max_attempts;
    }
}
