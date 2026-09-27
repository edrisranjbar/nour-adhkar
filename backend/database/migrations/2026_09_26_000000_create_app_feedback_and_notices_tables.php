<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('app_feedback', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20);
            $table->text('message');
            $table->timestamps();
        });
        Schema::create('app_notices', function (Blueprint $table) {
            $table->id();
            $table->string('title', 160);
            $table->text('message');
            $table->boolean('published')->default(false);
            $table->timestamps();
        });
        Schema::create('app_notice_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notice_id')->constrained('app_notices')->cascadeOnDelete();
            $table->uuid('installation_id');
            $table->timestamp('read_at');
            $table->unique(['notice_id', 'installation_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_notice_reads');
        Schema::dropIfExists('app_notices');
        Schema::dropIfExists('app_feedback');
    }
};
