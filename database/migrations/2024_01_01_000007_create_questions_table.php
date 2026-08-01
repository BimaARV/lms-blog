<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ============================================================
        // QUESTIONS — soal
        // type: single | multiple | essay
        // ============================================================
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();

            $table->json('body');                                  // {"id":"...","en":"..."}
            $table->string('type', 20);                            // single|multiple|essay
            $table->json('options')->nullable();                   // untuk single/multiple: {"id":["A","B","C","D"],"en":[...]}
            $table->json('correct_answers')->nullable();           // untuk single/multiple: ["A","B"]
            $table->text('sample_answer')->nullable();             // untuk essay (panduan jawab)
            $table->unsignedInteger('score')->default(1);
            $table->text('explanation')->nullable();
            $table->string('image')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['exam_id', 'type']);
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
