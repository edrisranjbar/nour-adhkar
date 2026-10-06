<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('instagram_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->json('design');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
        $base = ['font' => 'vazirmatn', 'font_size' => 60, 'line_height' => 1.85, 'margin' => 125, 'logo_size' => 76, 'layout' => 'centered'];
        foreach ([
            ['کاغذی', '#F6F3EC', '#243D34', '#69776C', true],
            ['سفید', '#FFFFFF', '#243D34', '#69776C', false],
            ['شب', '#172C25', '#F6F3EC', '#C0CBC3', false],
        ] as [$name, $background, $text, $muted, $default]) {
            DB::table('instagram_templates')->insert(['name' => $name, 'design' => json_encode($base + compact('background', 'text', 'muted')), 'is_default' => $default, 'created_at' => now(), 'updated_at' => now()]);
        }
        Schema::table('instagram_posts', function (Blueprint $table) {
            $table->foreignId('template_id')->nullable()->constrained('instagram_templates')->nullOnDelete();
            $table->json('design')->nullable();
        });
        // Existing images retain their original appearance; later template edits do not alter them.
        $templates = DB::table('instagram_templates')->orderBy('id')->get();
        foreach (['paper', 'white', 'night'] as $index => $theme) {
            DB::table('instagram_posts')->where('theme', $theme)->update(['template_id' => $templates[$index]->id, 'design' => $templates[$index]->design]);
        }
    }

    public function down(): void
    {
        Schema::table('instagram_posts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('template_id');
            $table->dropColumn('design');
        });
        Schema::dropIfExists('instagram_templates');
    }
};
