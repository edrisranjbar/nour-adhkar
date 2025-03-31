<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('default_dhikrs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->integer('count');
            $table->string('prefix')->nullable();
            $table->string('suffix')->nullable();
            $table->string('translation');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('default_dhikrs');
    }
}; 