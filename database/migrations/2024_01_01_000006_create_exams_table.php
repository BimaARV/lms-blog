<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ============================================================
        // EXAMS — ujian
        // ============================================================
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();

            $table->json('title');
            $table->json('description')->nullable();
            $table->unsignedInteger('duration_minutes')->default(120); // default 2 jam
            $table->unsignedInteger('passing_score')->default(70);    // 0-100
            $table->unsignedInteger('max_attempts')->default(1);      // 0 = unlimited
            $table->boolean('shuffle_questions')->default(true);
            $table->boolean('shuffle_answers')->default(true);
            $table->boolean('show_result_immediately')->default(true);
            $table->boolean('require_enrollment')->default(true);
            $table->timestamp('available_from')->nullable();
            $table->timestamp('available_until')->nullable();
            $table->string('status', 20)->default('draft');     // draft|published|archived
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
