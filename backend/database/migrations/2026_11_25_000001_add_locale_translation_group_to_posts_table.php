<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('locale', 3)->nullable()->after('slug');
            $table->string('translation_group', 36)->nullable()->after('locale')->index();
        });

        $groups = [];
        $usedLocales = [];
        DB::table('posts')->select('id', 'slug', 'content')->orderBy('id')->chunkById(200, function ($posts) use (&$groups, &$usedLocales) {
            foreach ($posts as $post) {
                $locale = 'fa';
                $groupKey = null;
                $metadata = (string) $post->content;

                if (preg_match('/(?:^|[\\r\\n])\\s*locale\\s*:\\s*(fa|ar|ckb)\\b/iu', $metadata, $match)) {
                    $locale = strtolower($match[1]);
                }
                if (preg_match('/(?:^|[\\r\\n])\\s*translation_group\\s*:\\s*([a-z0-9._-]+)/iu', $metadata, $match)) {
                    $groupKey = strtolower($match[1]);
                }
                if (!$groupKey && preg_match('/^(.*)-(fa|ar|ckb)(?:-draft-\\d{8})?$/i', (string) $post->slug, $match)) {
                    $locale = strtolower($match[2]);
                    $groupKey = strtolower($match[1]);
                }

                $groupKey ??= 'legacy-post-' . $post->id;
                if (isset($usedLocales[$groupKey][$locale])) {
                    $groupKey .= '-post-' . $post->id;
                }
                $groups[$groupKey] ??= (string) Str::uuid();
                $usedLocales[$groupKey][$locale] = true;
                DB::table('posts')->where('id', $post->id)->update([
                    'locale' => $locale,
                    'translation_group' => $groups[$groupKey],
                ]);
            }
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->unique(['translation_group', 'locale'], 'posts_translation_locale_unique');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropUnique('posts_translation_locale_unique');
            $table->dropIndex(['translation_group']);
            $table->dropColumn(['locale', 'translation_group']);
        });
    }
};
