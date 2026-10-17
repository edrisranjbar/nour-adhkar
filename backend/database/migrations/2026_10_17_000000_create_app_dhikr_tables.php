<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * «اذکار برنامه»: adhkar added from the admin panel and served to the app by GET /api/app-adhkar.
 * A collection either extends one of the app's built-in collections (builtin_key, e.g. "morning")
 * or is a new collection of its own. Separate from the website's older adhkars/collections tables.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_dhikr_collections', function (Blueprint $table) {
            $table->id();
            $table->string('builtin_key', 40)->nullable();  // app collection id, or null for a new collection
            $table->string('title', 160)->nullable();       // required for new collections
            $table->string('description', 300)->nullable();
            $table->string('icon', 40)->default('menu_book');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('published')->default(true);
            $table->timestamps();
        });

        Schema::create('app_dhikrs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->constrained('app_dhikr_collections')->cascadeOnDelete();
            $table->text('arabic_text');
            $table->text('translation')->nullable();        // Persian meaning
            $table->string('source', 500)->nullable();
            $table->unsignedSmallInteger('target_count')->default(1);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('published')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_dhikrs');
        Schema::dropIfExists('app_dhikr_collections');
    }
};
