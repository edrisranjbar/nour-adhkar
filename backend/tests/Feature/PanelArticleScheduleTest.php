<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('schedules an article and hides it from the app until its publish time', function () {
    Carbon::setTestNow(Carbon::parse('2026-10-03 08:00', 'UTC'));
    $admin = User::factory()->create(['role' => 'admin', 'active' => true]);

    // 2026-10-10 12:00 Tehran (UTC+3:30) is 08:30 UTC.
    $this->actingAs($admin, 'admin')->post('/admin/articles', [
        'title' => 'موعظه هفته', 'content' => 'متن', 'published' => '1', 'publish_at' => '2026-10-10T12:00',
    ])->assertRedirect(route('panel.articles.index'));

    $article = DB::table('posts')->first();
    expect($article->status)->toBe('published')
        ->and(Carbon::parse($article->published_at, 'UTC')->toDateTimeString())->toBe('2026-10-10 08:30:00');

    $this->actingAs($admin, 'admin')->get('/admin/articles')->assertSee('زمان‌بندی‌شده');
    $this->getJson('/api/app-articles')->assertOk()->assertJsonCount(0, 'data');

    Carbon::setTestNow(Carbon::parse('2026-10-10 08:31', 'UTC'));
    $this->getJson('/api/app-articles')->assertOk()->assertJsonCount(1, 'data');
});

it('publishes immediately when no publish time is given', function () {
    $admin = User::factory()->create(['role' => 'admin', 'active' => true]);
    $this->actingAs($admin, 'admin')->post('/admin/articles', ['title' => 'الف', 'content' => 'متن', 'published' => '1']);
    $this->getJson('/api/app-articles')->assertJsonCount(1, 'data');
});
