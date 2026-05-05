<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'heart_score')) {
                $table->dropColumn('heart_score');
            }
            if (Schema::hasColumn('users', 'score')) {
                $table->dropColumn('score');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'heart_score')) {
                $table->integer('heart_score')->default(0);
            }
            if (!Schema::hasColumn('users', 'score')) {
                $table->integer('score')->default(0);
            }
        });
    }
};
