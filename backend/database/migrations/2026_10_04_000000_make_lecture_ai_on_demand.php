<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Lecture transcripts become on demand: an admin presses «ساخت توضیح از روی صوت» on the edit form,
 * the lecture-ai workflow transcribes and summarizes it, and the result is written into the
 * description. Lectures are no longer queued automatically, and descriptions can now hold a full
 * transcript.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lectures', function (Blueprint $table) {
            $table->longText('description')->nullable()->change();
            $table->string('ai_status', 20)->default('idle')->change();
        });
        DB::table('lectures')->whereIn('ai_status', ['pending', 'done'])->update(['ai_status' => 'idle']);
    }

    public function down(): void
    {
        Schema::table('lectures', function (Blueprint $table) {
            $table->string('ai_status', 20)->default('pending')->change();
        });
    }
};
