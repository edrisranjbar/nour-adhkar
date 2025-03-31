<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('dhikrs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->integer('count');
            $table->timestamp('completed_at');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('dhikrs');
    }
}; 