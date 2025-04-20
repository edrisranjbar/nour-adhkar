<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_dhikrs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('arabic_text');
            $table->text('translation');
            $table->text('transliteration');
            $table->integer('count')->default(1);
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_dhikrs');
    }
}; 