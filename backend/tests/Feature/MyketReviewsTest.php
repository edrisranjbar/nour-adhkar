<?php

use App\Models\User;
use App\Services\StoreStats\MyketInstallProvider;
use App\Services\StoreStats\MyketReviews;
use App\Services\StoreStats\StoreStats;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

function myketReviewPage(): string {
    return '<script type="application/ld+json">'.json_encode([
        '@type'=>'SoftwareApplication',
        'aggregateRating'=>['ratingValue'=>4.5, 'ratingCount'=>20],
        'review'=>[['author'=>['name'=>'Myket reader'], 'reviewBody'=>'A helpful app <script>bad</script>',
            'reviewRating'=>['ratingValue'=>4]]],
    ]).'</script><tr><td>تعداد دانلود</td><td>۲۰۰</td></tr>';
}

beforeEach(function () {
    Cache::flush();
    $this->myketResponse = (object) ['body' => myketReviewPage()];
    Http::fake([
        'myket.ir/*'=>fn () => Http::response($this->myketResponse->body),
        'cafebazaar.ir/*'=>Http::response('<dl><dt>نصب</dt><dd>۱.۳ هزار</dd></dl>'),
        'api.cafebazaar.ir/*'=>Http::response(['properties'=>['statusCode'=>200], 'singleReply'=>['reviewReply'=>['reviews'=>[]]]]),
    ]);
});

it('reads Myket rating and comments from the same cached public page', function () {
    $metrics = app(MyketInstallProvider::class)->metrics();
    expect($metrics)->toBe(['installs'=>200, 'rating'=>4.5, 'rating_count'=>20]);
    $service = app(MyketReviews::class);
    expect($service->current()['stale'])->toBeFalse()
        ->and($service->query()->first()->message)->toContain('A helpful app')
        ->and($service->query()->first()->date_label)->toBeNull();
    $service->current();
    Http::assertSentCount(1);
    expect($service->query()->count())->toBe(1);
});

it('uses vote weighted ratings and refuses an incomplete combined rating', function () {
    $stores = ['bazaar'=>['rating'=>5, 'rating_count'=>80, 'rating_stale'=>false],
        'myket'=>['rating'=>4, 'rating_count'=>20, 'rating_stale'=>true]];
    expect(StoreStats::ratingSummary($stores))->toBe(['rating'=>4.8, 'rating_count'=>100, 'rating_stale'=>true]);
    unset($stores['myket']);
    expect(StoreStats::ratingSummary($stores)['rating'])->toBeNull();
});

it('retains comments and marks stale when Myket metadata disappears', function () {
    $service = app(MyketReviews::class);
    $service->current();
    Cache::flush();
    $this->myketResponse = (object) ['body' => myketReviewPage()];
    $this->myketResponse->body = '<html>layout changed</html>';
    expect($service->current()['stale'])->toBeTrue()->and($service->query()->count())->toBe(1);
});

it('shows escaped Myket comments on the dashboard and source filtered feedback page', function () {
    $this->actingAs(User::factory()->create(['role'=>'admin', 'active'=>true]), 'admin');
    $this->get('/admin')->assertOk()->assertSee('id="myket-reviews"', false)->assertSee('Myket reader');
    $this->get('/admin/feedback?source=myket')->assertOk()->assertSee('Myket reader')
        ->assertSee('&lt;script&gt;bad&lt;/script&gt;', false)
        ->assertDontSee('نظرات کافه‌بازار')->assertDontSee('بازخوردهای درون برنامه');
    $this->getJson('/admin/installs')->assertOk()->assertJsonPath('rating_summary.rating', null)
        ->assertJsonPath('stores.myket.rating_count', 20);
});

