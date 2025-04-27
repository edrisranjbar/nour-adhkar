<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add last_activity_date column if it doesn't exist
            if (!Schema::hasColumn('users', 'last_activity_date')) {
                $table->timestamp('last_activity_date')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'last_activity_date')) {
                $table->dropColumn('last_activity_date');
            }
        });
    }
};
