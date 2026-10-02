<?php

use App\Models\User;
use App\Services\StoreStats\BazaarReviews;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

function reviewFeed(array $reviews): array
{
    return ['properties' => ['statusCode' => 200], 'singleReply' => ['reviewReply' => ['reviews' => $reviews]]];
}

function storeReview(int $id, string $message = 'نظر بازار', string $date = '۱۴۰۵/۰۷/۰۹'): array
{
    return ['id' => $id, 'user' => 'کاربر بازار', 'comment' => $message, 'rate' => 5, 'date' => $date];
}

beforeEach(function () {
    Cache::flush();
    Http::preventStrayRequests();
});

it('imports once per cache window, updates edited text and preserves the first import time', function () {
    Http::fakeSequence()->push(reviewFeed([storeReview(12)]))->push(reviewFeed([storeReview(12, 'ویرایش‌شده')]));
    $reviews = app(BazaarReviews::class);
    expect($reviews->current()['count'])->toBe(1);
    $first = $reviews->query()->first()->first_seen_at;
    $reviews->current();
    Http::assertSentCount(1);
    $this->travel(11)->minutes();
    expect($reviews->current()['count'])->toBe(1)
        ->and($reviews->query()->first()->message)->toBe('ویرایش‌شده')
        ->and($reviews->query()->first()->first_seen_at)->toBe($first);
});

it('sorts by published Jalali date rather than featured-feed order and excludes ratings without text', function () {
    Http::fakeSequence()->push(reviewFeed([storeReview(11, 'قدیمی', '۱۴۰۵/۰۶/۲۲'), storeReview(12, 'جدید'), storeReview(13, '')]));
    $reviews = app(BazaarReviews::class);
    expect($reviews->current()['count'])->toBe(2)
        ->and($reviews->query()->pluck('review_id')->all())->toBe(['12', '11']);
});

it('preserves reviews on transport errors or malformed responses and marks them stale', function () {
    Http::fakeSequence()->push(reviewFeed([storeReview(12)]))->push([], 503)->push(['singleReply' => []]);
    $reviews = app(BazaarReviews::class);
    $reviews->current();
    foreach ([1, 2] as $attempt) {
        Cache::flush();
        expect($reviews->current()['stale'])->toBeTrue()
            ->and($reviews->current()['updated_at'])->not->toBeNull()
            ->and($reviews->query()->count())->toBe(1);
    }
});

it('bounds paging and deduplicates repeated featured reviews', function () {
    $page = array_map(fn ($id) => storeReview($id), range(1, 24));
    Http::fakeSequence()->push(reviewFeed($page))->push(reviewFeed($page))->push(reviewFeed($page));
    expect(app(BazaarReviews::class)->current()['count'])->toBe(24);
    Http::assertSentCount(3);
    Http::assertSent(fn ($request) => $request['singleRequest']['reviewRequest']['start'] === 48);
});

it('scopes saved reviews to the configured package', function () {
    Http::fakeSequence()->push(reviewFeed([storeReview(12)]))->push(reviewFeed([]));
    $reviews = app(BazaarReviews::class);
    $reviews->current();
    config(['stores.package' => 'com.example.other']);
    expect($reviews->current()['count'])->toBe(0);
    Http::assertSent(fn ($request) => $request['singleRequest']['reviewRequest']['packageName'] === 'com.example.other');
});

it('shows escaped read-only Bazaar reviews on the dashboard and feedback page with source filters', function () {
    Http::fake(['*' => Http::response(reviewFeed([storeReview(12, '<script>alert(1)</script>')]))]);
    DB::table('app_feedback')->insert(['type' => 'suggestion', 'message' => 'بازخورد داخل برنامه', 'created_at' => now(), 'updated_at' => now()]);
    $admin = User::factory()->create(['role' => 'admin', 'active' => true]);
    $this->actingAs($admin, 'admin')->get('/admin/feedback')->assertOk()
        ->assertSee('بازخورد داخل برنامه')->assertSee('&lt;script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
    $this->get('/admin/feedback?source=bazaar')->assertOk()->assertDontSee('بازخورد داخل برنامه')
        ->assertDontSee('name="_method" value="DELETE"', false)->assertSee('فقط نمایش');
    $this->get('/admin/feedback?source=app')->assertOk()->assertSee('بازخورد داخل برنامه')->assertDontSee('&lt;script&gt;', false);
    $this->get('/admin')->assertOk()->assertSee('&lt;script&gt;', false);
});

it('rejects unauthenticated access before fetching reviews', function () {
    $this->get('/admin/feedback')->assertRedirect('/admin/login');
    $this->get('/admin/installs')->assertRedirect('/admin/login');
    Http::assertNothingSent();
});

it('returns refreshed safely rendered reviews with the dashboard polling feed', function () {
    Http::fake([
        'api.cafebazaar.ir/*' => Http::response(reviewFeed([storeReview(12, 'متن تازه')])),
        'cafebazaar.ir/*' => Http::response('<html>no metrics</html>'),
    ]);
    $admin = User::factory()->create(['role' => 'admin', 'active' => true]);
    $response = $this->actingAs($admin, 'admin')->getJson('/admin/installs')->assertOk()
        ->assertJsonPath('bazaar_reviews.count', 1)->assertJsonPath('bazaar_reviews.stale', false);
    expect($response->json('bazaar_reviews_html'))->toContain('متن تازه');
});

it('keeps Bazaar pagination independent from the in-app filter and leaves stored reviews untouched', function () {
    Http::fakeSequence()->push(reviewFeed([]));
    foreach (range(1, 31) as $id) {
        DB::table('store_reviews')->insert([
            'store' => 'bazaar', 'package' => config('stores.package'), 'review_id' => (string) $id,
            'author' => 'کاربر', 'message' => 'نظر '.$id, 'date_sort' => '1405/07/09',
            'first_seen_at' => now(), 'last_seen_at' => now(),
        ]);
    }
    $admin = User::factory()->create(['role' => 'admin', 'active' => true]);
    $response = $this->actingAs($admin, 'admin')->get('/admin/feedback?source=bazaar&bazaar_page=2&type=suggestion')->assertOk();
    expect($response->viewData('bazaarItems')->currentPage())->toBe(2)
        ->and($response->viewData('bazaarItems')->count())->toBe(1)
        ->and($response->viewData('bazaarItems')->total())->toBe(31);
    $response->assertDontSee('name="_method" value="DELETE"', false);
});

it('does not publish a partially fetched batch when a later page fails', function () {
    Http::fakeSequence()->push(reviewFeed(array_map(fn ($id) => storeReview($id), range(1, 24))))->push([], 503);
    expect(app(BazaarReviews::class)->current()['stale'])->toBeTrue()
        ->and(DB::table('store_reviews')->count())->toBe(0);
});
