<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_dhikrs', function (Blueprint $table) {
            $table->boolean('is_completed')->default(false);
            $table->integer('completed_count')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('user_dhikrs', function (Blueprint $table) {
            $table->dropColumn(['is_completed', 'completed_count']);
        });
    }
}; 