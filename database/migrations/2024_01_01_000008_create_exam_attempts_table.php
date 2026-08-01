<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ============================================================
        // EXAM ENROLLMENTS — user daftar ikut ujian
        // ============================================================
        Schema::create('exam_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('attempts_used')->default(0);
            $table->timestamp('enrolled_at')->useCurrent();

            $table->unique(['exam_id', 'user_id']);
        });

        // ============================================================
        // EXAM ATTEMPTS — percobaan ujian (jawaban + score)
        // ============================================================
        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->json('question_snapshot');         // snapshot soal saat ujian (anti-change after submit)
            $table->json('answers')->nullable();       // {"question_id": {"type":"multiple","selected":["A","C"]} | {"type":"essay","text":"..."}}
            $table->unsignedInteger('score')->default(0);
            $table->unsignedInteger('max_score');
            $table->timestamp('started_at');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('expires_at')->nullable();           // started + duration
            $table->string('status', 20)->default('in_progress');  // in_progress|submitted|expired|graded
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'exam_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_attempts');
        Schema::dropIfExists('exam_enrollments');
    }
};
