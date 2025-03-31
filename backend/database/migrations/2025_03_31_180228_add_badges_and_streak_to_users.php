<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('badges')->nullable();
            $table->integer('total_dhikrs')->default(0);
            $table->integer('streak')->default(0);
            $table->date('last_dhikr_date')->nullable();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['badges', 'total_dhikrs', 'streak', 'last_dhikr_date']);
        });
    }
};
