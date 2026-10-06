<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('instagram_posts', function (Blueprint $table) {
            $table->id();
            $table->string('verse_key')->unique();
            $table->text('caption');
            $table->string('theme')->default('paper');
            $table->string('status')->default('draft')->index();
            $table->unsignedInteger('position')->default(0)->index();
            $table->string('instagram_url', 500)->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('instagram_posts'); }
};
