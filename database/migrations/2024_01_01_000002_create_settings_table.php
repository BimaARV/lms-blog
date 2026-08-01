<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ============================================================
        // SETTINGS TABLE — semua konfigurasi dinamis (bukan .env)
        // ============================================================
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('group', 50)->index();          // general, smtp, baileys, social, seo, appearance, etc.
            $table->string('key', 100);
            $table->text('value')->nullable();
            $table->string('type', 30)->default('string'); // string, text, boolean, integer, json, file
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['group', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
