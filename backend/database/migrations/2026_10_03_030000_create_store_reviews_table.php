<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_reviews', function (Blueprint $table) {
            $table->id();
            $table->string('store', 30);
            $table->string('package');
            $table->string('review_id', 60);
            $table->string('author')->nullable();
            $table->text('message');
            $table->unsignedTinyInteger('rating')->nullable();
            $table->string('date_label', 40)->nullable();
            $table->string('date_sort', 10)->nullable();
            $table->timestamp('first_seen_at');
            $table->timestamp('last_seen_at');
            $table->unique(['store', 'package', 'review_id']);
            $table->index(['store', 'package', 'date_sort']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_reviews');
    }
};
