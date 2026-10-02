<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Transcript and summary for each lecture, produced off-server by the lecture-ai GitHub workflow
 * (Groq: Whisper for speech-to-text, an LLM for the summary) and reviewed in the admin panel.
 * Nothing is shown in the app until an admin ticks text_published.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lectures', function (Blueprint $table) {
            $table->longText('transcript')->nullable();
            $table->text('summary')->nullable();
            // pending → processing → done | failed. Existing lectures start as pending.
            $table->string('ai_status', 20)->default('pending');
            $table->string('ai_error', 500)->nullable();
            $table->timestamp('ai_claimed_at')->nullable();
            $table->timestamp('ai_processed_at')->nullable();
            $table->boolean('text_published')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('lectures', function (Blueprint $table) {
            $table->dropColumn(['transcript', 'summary', 'ai_status', 'ai_error', 'ai_claimed_at', 'ai_processed_at', 'text_published']);
        });
    }
};
