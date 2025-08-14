<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('page_visits', function (Blueprint $table) {
            $table->string('browser', 50)->nullable()->after('user_agent');
            $table->string('country', 100)->nullable()->after('browser');
            $table->string('country_code', 2)->nullable()->after('country');
        });
    }

    public function down(): void
    {
        Schema::table('page_visits', function (Blueprint $table) {
            $table->dropColumn(['browser', 'country', 'country_code']);
        });
    }
};


