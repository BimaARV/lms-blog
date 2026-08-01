<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class Question extends Model
{
    use HasFactory;
    use HasTranslations;

    public array $translatable = ['body', 'options', 'correct_answers', 'sample_answer', 'explanation'];

    protected $fillable = [
        'exam_id', 'body', 'type', 'options', 'correct_answers',
        'sample_answer', 'score', 'explanation', 'image', 'sort_order',
    ];

    protected $casts = [
        'score' => 'integer',
        'sort_order' => 'integer',
    ];

    public const TYPES = [
        'single'   => 'Pilihan Ganda (1 jawaban)',
        'multiple' => 'Pilihan Ganda Kompleks (banyak jawaban)',
        'essay'    => 'Esai',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function getLocalizedOptions(): array
    {
        $locale = app()->getLocale();
        $opts = $this->options;

        if (is_array($opts) && isset($opts[$locale]) && is_array($opts[$locale])) {
            return $opts[$locale];
        }
        return is_array($opts) ? array_values($opts) : [];
    }

    public function getLocalizedCorrectAnswers(): array
    {
        $locale = app()->getLocale();
        $ans = $this->correct_answers;

        if (is_array($ans) && isset($ans[$locale])) {
            return is_array($ans[$locale]) ? $ans[$locale] : [$ans[$locale]];
        }
        return is_array($ans) ? $ans : [];
    }

    public function isCorrect(array $userAnswer): bool
    {
        if ($this->type === 'essay') {
            return null; // essay butuh manual grading
        }

        $correct = $this->getLocalizedCorrectAnswers();
        sort($correct);
        $given = is_array($userAnswer) ? $userAnswer : [$userAnswer];
        $given = array_values(array_filter($given));
        sort($given);

        return $correct === $given;
    }
}
