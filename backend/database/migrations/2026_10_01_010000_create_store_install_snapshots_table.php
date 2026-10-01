<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // One row per observed change of a store's public install count (plus a periodic heartbeat).
        Schema::create('store_install_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('store', 20);
            $table->unsignedBigInteger('installs');
            $table->timestamp('recorded_at');
            $table->index(['store', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_install_snapshots');
    }
};
