<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

require_once __DIR__.'/../../../deploy/schedule-articles.php';

uses(RefreshDatabase::class);

beforeEach(function () {
    DB::table('posts')->insert([
        'user_id' => User::factory()->create(['role' => 'admin'])->id,
        'title' => 'مرجع', 'slug' => 'daily-adhkar-start', 'content' => 'محتوای مرجع', 'status' => 'published', 'published_at' => now()->subDay(),
    ]);
    $this->batch = ['timezone' => 'Asia/Tehran', 'reference_slug' => 'daily-adhkar-start', 'articles' => [
        ['title' => 'مقاله اول', 'slug' => 'batch-one', 'excerpt' => 'خلاصه', 'content' => 'متن اول', 'publish_at' => '2099-10-26T09:00'],
        ['title' => 'مقاله دوم', 'slug' => 'batch-two', 'excerpt' => 'خلاصه', 'content' => 'متن دوم', 'publish_at' => '2099-10-28T09:00'],
    ]];
});

it('schedules both articles in UTC and safely accepts the same batch again', function () {
    $rows = ScheduledArticleBatch::import($this->batch);
    expect($rows[0]['published_at_utc'])->toBe('2099-10-26 05:30:00')
        ->and($rows[1]['published_at_utc'])->toBe('2099-10-28 05:30:00');
    expect(ScheduledArticleBatch::import($this->batch))->toBe($rows);
    expect(DB::table('posts')->count())->toBe(3);
    $this->getJson('/api/posts/batch-one')->assertNotFound();
    $this->getJson('/api/app-articles')->assertOk()->assertJsonCount(1, 'data');
});

it('rolls back the entire batch on a conflicting slug', function () {
    DB::table('posts')->insert(['user_id' => DB::table('posts')->first()->user_id, 'title' => 'موجود', 'slug' => 'batch-two', 'content' => 'حفظ شود', 'status' => 'draft']);
    expect(fn () => ScheduledArticleBatch::import($this->batch))->toThrow(InvalidArgumentException::class);
    expect(DB::table('posts')->where('slug', 'batch-one')->exists())->toBeFalse()
        ->and(DB::table('posts')->where('slug', 'batch-two')->value('content'))->toBe('حفظ شود');
});

it('rejects past or non-two-day publication intervals before writing anything', function () {
    $past = $this->batch;
    $past['articles'][0]['publish_at'] = '2000-01-01T09:00';
    expect(fn () => ScheduledArticleBatch::import($past))->toThrow(InvalidArgumentException::class);
    $this->batch['articles'][1]['publish_at'] = '2099-10-29T09:00';
    expect(fn () => ScheduledArticleBatch::import($this->batch))->toThrow(InvalidArgumentException::class);
    expect(DB::table('posts')->count())->toBe(1);
});
