<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('user_badges');
        Schema::dropIfExists('badges');

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'league_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropConstrainedForeignId('league_id');
            });
        }

        Schema::dropIfExists('leagues');

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'badges')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('badges');
            });
        }
    }

    public function down(): void
    {
        //
    }
};
