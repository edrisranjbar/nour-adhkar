<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Who sent each in-app feedback: the signed-in user, or null for older app versions without sign-in. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_feedback', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('app_feedback', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
