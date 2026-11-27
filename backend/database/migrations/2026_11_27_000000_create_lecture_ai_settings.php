<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lecture_ai_settings', function (Blueprint $table) {
            $table->id();
            $table->string('base_url')->default('https://ai.manageit.dev/api/v1');
            $table->text('api_key')->nullable();
            $table->string('transcription_model')->nullable();
            $table->string('summary_model')->nullable();
            $table->json('models')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('lecture_ai_settings'); }
};
