<?php

use App\Support\AppDhikrCatalog;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

final class AppAdhkarBatch
{
    public static function import(array $input): array
    {
        $batch = Validator::make($input, [
            'collections' => 'required|array|size:3',
            'collections.*.title' => 'required|string|max:160|distinct',
            'collections.*.description' => 'required|string|max:300',
            'collections.*.icon' => ['required', Rule::in(array_keys(AppDhikrCatalog::ICONS))],
            'collections.*.items' => 'required|array|min:1|max:3',
            'collections.*.items.*.arabic_text' => 'required|string|max:10000',
            'collections.*.items.*.translation' => 'required|string|max:10000',
            'collections.*.items.*.source' => 'required|string|max:500',
            'collections.*.items.*.target_count' => 'required|integer|in:1',
        ])->validate();

        return DB::transaction(function () use ($batch) {
            $result = [];
            $knownTexts = DB::table('app_dhikrs')->get()->keyBy(fn ($item) => self::normalize($item->arabic_text));
            foreach ($batch['collections'] as $collection) {
                $expected = [
                    'builtin_key' => null, 'title' => $collection['title'],
                    'description' => $collection['description'], 'icon' => $collection['icon'], 'published' => true,
                ];
                $existing = DB::table('app_dhikr_collections')->where('title', $collection['title'])->get();
                if ($existing->count() > 1) throw new RuntimeException('Ambiguous collection title; refusing to import.');
                if ($existing->isNotEmpty()) {
                    $saved = $existing->first();
                    self::assertMatches($saved, $expected);
                    $collectionId = $saved->id;
                } else {
                    $collectionId = DB::table('app_dhikr_collections')->insertGetId($expected + [
                        'sort_order' => (int) DB::table('app_dhikr_collections')->max('sort_order') + 10,
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
                $ids = [];
                foreach ($collection['items'] as $item) {
                    $expectedItem = $item + ['collection_id' => $collectionId, 'published' => true];
                    $normalized = self::normalize($item['arabic_text']);
                    if ($knownTexts->has($normalized)) {
                        $savedItem = $knownTexts->get($normalized);
                        self::assertMatches($savedItem, $expectedItem);
                        $id = $savedItem->id;
                    } else {
                        $id = DB::table('app_dhikrs')->insertGetId($expectedItem + [
                            'sort_order' => (int) DB::table('app_dhikrs')->where('collection_id', $collectionId)->max('sort_order') + 10,
                            'created_at' => now(), 'updated_at' => now(),
                        ]);
                        $knownTexts->put($normalized, DB::table('app_dhikrs')->find($id));
                    }
                    self::assertMatches(DB::table('app_dhikrs')->find($id), $expectedItem);
                    $ids[] = $id;
                }
                $result[] = ['id' => $collectionId, 'title' => $collection['title'], 'dhikr_ids' => $ids];
            }
            return $result;
        });
    }

    private static function normalize(string $text): string
    {
        return preg_replace('/[\p{M}\p{P}\s\x{0640}]/u', '', $text);
    }

    private static function assertMatches(object $saved, array $expected): void
    {
        foreach ($expected as $field => $value) {
            if ((string) $saved->$field !== (string) $value) {
                throw new RuntimeException('Existing record differs; refusing to overwrite: '.$field);
            }
        }
    }
}

if (isset($_SERVER['SCRIPT_FILENAME']) && realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__) {
    $backend = getenv('NOUR_BACKEND_ROOT');
    if (!$backend || empty($argv[1])) throw new InvalidArgumentException('Backend root and batch path required.');
    require $backend.'/vendor/autoload.php';
    $app = require $backend.'/bootstrap/app.php';
    $app->make(Kernel::class)->bootstrap();
    $input = json_decode(file_get_contents($argv[1]), true, 512, JSON_THROW_ON_ERROR);
    echo json_encode(AppAdhkarBatch::import($input), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT).PHP_EOL;
}
