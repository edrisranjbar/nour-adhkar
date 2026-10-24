<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

require_once __DIR__.'/../../../deploy/import-app-adhkar.php';

function reviewedAdhkarBatch(): array
{
    return json_decode(file_get_contents(__DIR__.'/../../../deploy/content/app-adhkar-2026-10-24.json'), true, 512, JSON_THROW_ON_ERROR);
}

it('publishes the reviewed batch in the app API and reruns without duplicates', function () {
    AppAdhkarBatch::import(reviewedAdhkarBatch());
    AppAdhkarBatch::import(reviewedAdhkarBatch());
    expect(DB::table('app_dhikr_collections')->count())->toBe(3);
    expect(DB::table('app_dhikrs')->count())->toBe(8);
    $this->getJson('/api/app-adhkar')->assertOk()->assertJsonCount(3, 'data')
        ->assertJsonCount(3, 'data.0.items')->assertJsonCount(3, 'data.1.items')->assertJsonCount(2, 'data.2.items')
        ->assertJsonPath('data.0.items.0.arabic', 'اللَّهُمَّ صَيِّبًا نَافِعًا')
        ->assertJsonPath('data.0.items.0.count', 1);
    foreach (DB::table('app_dhikrs')->get() as $item) {
        expect($item->source)->toContain('https://sunnah.com/');
    }
});

it('does not replace later admin edits or unhide records', function () {
    AppAdhkarBatch::import(reviewedAdhkarBatch());
    DB::table('app_dhikrs')->where('id', 1)->update(['published' => false, 'translation' => 'ویرایش مدیر']);
    expect(fn () => AppAdhkarBatch::import(reviewedAdhkarBatch()))->toThrow(RuntimeException::class);
    expect(DB::table('app_dhikrs')->find(1)->translation)->toBe('ویرایش مدیر');
    expect((bool) DB::table('app_dhikrs')->find(1)->published)->toBeFalse();
});

it('rolls back the entire batch if an existing collection conflicts', function () {
    $batch = reviewedAdhkarBatch();
    DB::table('app_dhikr_collections')->insert(['title' => $batch['collections'][1]['title'], 'description' => 'محتوای قبلی', 'published' => false]);
    expect(fn () => AppAdhkarBatch::import($batch))->toThrow(RuntimeException::class);
    expect(DB::table('app_dhikr_collections')->count())->toBe(1);
    expect(DB::table('app_dhikrs')->count())->toBe(0);
});
