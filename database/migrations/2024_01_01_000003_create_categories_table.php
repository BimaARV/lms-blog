<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ============================================================
        // CATEGORIES — multi-bahasa
        // ============================================================
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->json('name');                  // {"id":"Tutorial","en":"Tutorial"}
            $table->string('slug')->unique();
            $table->json('description')->nullable();
            $table->string('color', 20)->default('#00a8f4');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('categories')->nullOnDelete();
            $table->index('is_active');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
