<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('instagram_posts', function (Blueprint $table) {
            $table->text('image_text')->nullable();
            $table->string('translator_key')->default('khorramdel');
            $table->timestamp('scheduled_at')->nullable()->index();
            $table->string('image_path')->nullable();
            $table->uuid('publish_claim')->nullable();
            $table->timestamp('claimed_at')->nullable();
            $table->string('instagram_media_id')->nullable();
            $table->string('instagram_container_id')->nullable();
            $table->string('instagram_account_id')->nullable();
            $table->timestamp('publish_started_at')->nullable();
            $table->string('publish_error', 500)->nullable();
        });
        $catalog = json_decode(file_get_contents(resource_path('data/instagram-verses.json')), true);
        foreach ($catalog as $verse) {
            DB::table('instagram_posts')->where('verse_key', $verse['key'])->update([
                'image_text' => $verse['translations']['rowwad']['translation'], 'translator_key' => 'rowwad',
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('instagram_posts', fn (Blueprint $table) => $table->dropIndex(['scheduled_at']));
        Schema::table('instagram_posts', fn (Blueprint $table) => $table->dropColumn([
            'image_text', 'translator_key', 'scheduled_at', 'image_path', 'publish_claim', 'claimed_at', 'instagram_media_id', 'instagram_container_id', 'instagram_account_id', 'publish_started_at', 'publish_error',
        ]));
    }
};
