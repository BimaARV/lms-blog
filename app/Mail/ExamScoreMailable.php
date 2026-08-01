<?php

namespace App\Mail;

use App\Models\ExamAttempt;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ExamScoreMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $attempt;

    public function __construct(ExamAttempt $attempt)
    {
        $this->attempt = $attempt;
    }

    public function build()
    {
        return $this->subject('Hasil Ujian Lu Keluar, Goblok!')
            ->view('emails.exam-score')
            ->with([
                'examName' => $this->attempt->exam->title,
                'score' => $this->attempt->score,
                'maxScore' => $this->attempt->max_score,
            ]);
    }
}
