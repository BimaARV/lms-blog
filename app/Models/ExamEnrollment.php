<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamEnrollment extends Model
{
    protected $fillable = [
        'exam_id', 'user_id', 'attempts_used', 'enrolled_at',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'attempts_used' => 'integer',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public $timestamps = false;
}
