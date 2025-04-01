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
        // collections table
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->timestamps();
        });

        // dhikrs table
        Schema::create('adhkars', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('prefix');
            $table->text('arabic_text');
            $table->text('translation');
            $table->integer('count');
            $table->foreignId('collection_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
        });

        // collection_dhikr table (pivot)
        Schema::create('collection_adhkars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->constrained('collections');
            $table->foreignId('adhkar_id')->constrained('adhkars');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collection_adhkars');
        Schema::dropIfExists('adhkars');
        Schema::dropIfExists('collections');
    }
};
