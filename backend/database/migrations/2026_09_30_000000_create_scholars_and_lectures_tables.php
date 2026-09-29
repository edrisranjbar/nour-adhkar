<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * «علما و مشاهیر» in the app: scholars and their lectures, managed from the admin panel and
 * served by GET /api/scholars. Seeded with the two scholars the app bundles (same ids and hues).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scholars', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 40)->unique();      // stable id the app uses
            $table->string('name', 120);
            $table->string('tagline', 160)->nullable();
            $table->text('bio')->nullable();
            $table->unsignedSmallInteger('hue')->default(150); // 0..360, tints the cover art
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('published')->default(true);
            $table->timestamps();
        });

        Schema::create('lectures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scholar_id')->constrained('scholars')->cascadeOnDelete();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->string('audio_path')->nullable();    // uploaded file on the public disk
            $table->string('audio_url', 500)->nullable(); // or an external https link
            $table->unsignedInteger('duration_sec')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('published')->default(true);
            $table->timestamps();
        });

        foreach ([['ziaei', 'شیخ ضیایی', 152], ['pordel', 'شیخ پردل', 205]] as $i => [$slug, $name, $hue]) {
            DB::table('scholars')->insert([
                'slug' => $slug, 'name' => $name, 'tagline' => 'سخنرانی‌ها و دروس', 'bio' => null,
                'hue' => $hue, 'sort_order' => ($i + 1) * 10, 'published' => true,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lectures');
        Schema::dropIfExists('scholars');
    }
};
