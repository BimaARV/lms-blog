<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ============================================================
        // POSTS — blog
        // ============================================================
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();      // author
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();

            $table->json('title');           // {"id":"...","en":"..."}
            $table->json('slug');            // {"id":"...","en":"..."}
            $table->json('excerpt');         // ringkasan (optional)
            $table->json('body');            // markdown / html
            $table->json('meta_title')->nullable();
            $table->json('meta_description')->nullable();
            $table->string('featured_image')->nullable();

            $table->string('status', 20)->default('draft'); // draft|scheduled|published|archived
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('views_count')->default(0);
            $table->boolean('allow_comments')->default(true);
            $table->timestamps();

            $table->index('status');
            $table->index('published_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
