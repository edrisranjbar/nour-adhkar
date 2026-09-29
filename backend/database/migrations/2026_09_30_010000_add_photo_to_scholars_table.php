<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Scholar photos (uploaded in the admin panel) and Sheikh Pordel's full name. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scholars', function (Blueprint $table) {
            $table->string('photo_path')->nullable()->after('bio');
        });

        DB::table('scholars')->where('slug', 'pordel')->where('name', 'شیخ پردل')
            ->update(['name' => 'شیخ محمد صالح پردل', 'updated_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('scholars', function (Blueprint $table) {
            $table->dropColumn('photo_path');
        });
    }
};
