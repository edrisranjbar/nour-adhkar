<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('store_rating_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('store', 20);
            $table->decimal('rating', 3, 2);
            $table->unsignedBigInteger('rating_count');
            $table->timestamp('recorded_at');
            $table->index(['store', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_rating_snapshots');
    }
};
