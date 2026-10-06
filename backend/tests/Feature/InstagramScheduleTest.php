<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);
beforeEach(function () {
    config(['jwt.secret' => str_repeat('test-only-instagram-', 3)]);
    $this->actingAs(User::factory()->create(['role' => 'admin']), 'api');
    Storage::fake('public');
});
function instagramReady($test) {
    $post = $test->postJson('/api/admin/instagram-queue', ['verse_key' => '94:5'])->assertCreated()->json('data');
    return $test->putJson('/api/admin/instagram-queue/'.$post['id'], ['status' => 'queued'])->assertOk()->json('data');
}
function instagramImage() {
    return UploadedFile::fake()->image('post.jpg', 1080, 1350);
}
function instagramConfigured() {
    config(['services.instagram.enabled' => true, 'services.instagram.worker_token' => 'test-worker', 'services.instagram.account_id' => '123']);
}
it('keeps editable image text separate from the immutable translation source', function () {
    $post = instagramReady($this);
    expect($post['translator_key'])->toBe('khorramdel');
    $catalog = $this->getJson('/api/admin/instagram-queue')->json('catalog');
    $this->putJson('/api/admin/instagram-queue/'.$post['id'], ['image_text' => 'متن انتخابی', 'translator_key' => 'rowwad'])->assertOk()->assertJsonPath('data.image_text', 'متن انتخابی');
    expect($this->getJson('/api/admin/instagram-queue')->json('catalog'))->toBe($catalog);
});
it('preserves existing Rowwad content when adding editable content fields', function () {
    $post = instagramReady($this);
    $migration = require database_path('migrations/2026_10_06_000003_add_instagram_post_content_and_schedules.php');
    $migration->down();
    DB::table('instagram_posts')->where('id', $post['id'])->update(['status' => 'published', 'instagram_url' => 'https://www.instagram.com/p/Old/']);
    $migration->up();
    $saved = DB::table('instagram_posts')->find($post['id']);
    $verse = collect(json_decode(file_get_contents(resource_path('data/instagram-verses.json')), true))->firstWhere('key', '94:5');
    expect($saved->translator_key)->toBe('rowwad')->and($saved->image_text)->toBe($verse['translations']['rowwad']['translation'])->and($saved->caption)->toBe($post['caption'])->and($saved->status)->toBe('published');
});
it('rejects past dates and wrong image sizes before scheduling', function () {
    instagramConfigured(); $post = instagramReady($this);
    $path = '/api/admin/instagram-queue/'.$post['id'].'/schedule';
    $this->post($path, ['scheduled_local' => now('Asia/Tehran')->subDay()->format('Y-m-d\TH:i'), 'render_token' => $post['render_token'], 'image' => instagramImage()], ['Accept' => 'application/json'])->assertUnprocessable();
    $this->post($path, ['scheduled_local' => now('Asia/Tehran')->addDay()->format('Y-m-d\TH:i'), 'render_token' => $post['render_token'], 'image' => UploadedFile::fake()->image('small.jpg', 100, 100)], ['Accept' => 'application/json'])->assertUnprocessable();
    expect(DB::table('instagram_posts')->find($post['id'])->status)->toBe('queued');
});
it('requires configuration and detects stale rendered content', function () {
    $post = instagramReady($this);
    $this->postJson('/api/admin/instagram-queue/'.$post['id'].'/schedule', [])->assertUnprocessable();
    instagramConfigured();
    $this->post('/api/admin/instagram-queue/'.$post['id'].'/schedule', ['scheduled_local' => now('Asia/Tehran')->addDay()->format('Y-m-d\TH:i'), 'render_token' => str_repeat('0', 64), 'image' => instagramImage()], ['Accept' => 'application/json'])->assertConflict();
});
it('stores a Tehran schedule and locks its snapshot until cancellation', function () {
    instagramConfigured(); $post = instagramReady($this);
    $local = now('Asia/Tehran')->addDay()->startOfMinute();
    $this->post('/api/admin/instagram-queue/'.$post['id'].'/schedule', ['scheduled_local' => $local->format('Y-m-d\TH:i'), 'render_token' => $post['render_token'], 'image' => instagramImage()], ['Accept' => 'application/json'])->assertOk();
    $saved = DB::table('instagram_posts')->find($post['id']);
    expect($saved->scheduled_at)->toBe($local->clone()->utc()->format('Y-m-d H:i:s'));
    Storage::disk('public')->assertExists($saved->image_path);
    $this->putJson('/api/admin/instagram-queue/'.$post['id'], ['image_text' => 'changed'])->assertUnprocessable();
    $this->deleteJson('/api/admin/instagram-queue/'.$post['id'].'/schedule')->assertNoContent();
    Storage::disk('public')->assertMissing($saved->image_path);
    expect(DB::table('instagram_posts')->find($post['id'])->status)->toBe('queued');
});
it('authenticates workers and never retries an ambiguous publication', function () {
    instagramConfigured(); $post = instagramReady($this); $id = $post['id'];
    DB::table('instagram_posts')->where('id', $id)->update(['status' => 'scheduled', 'scheduled_at' => now()->subMinute(), 'instagram_account_id' => '123', 'image_path' => 'instagram-posts/test.jpg']);
    $this->postJson('/api/internal/instagram/claim', ['account_id' => '123'])->assertUnauthorized();
    $this->withToken('test-worker')->postJson('/api/internal/instagram/claim', ['account_id' => '456'])->assertUnprocessable();
    $claim = $this->postJson('/api/internal/instagram/claim', ['account_id' => '123'])->assertOk()->json('data.claim');
    $this->postJson('/api/internal/instagram/claim', ['account_id' => '123'])->assertJsonPath('data', null);
    $report = '/api/internal/instagram/'.$id.'/report';
    $this->postJson($report, ['claim' => $claim, 'stage' => 'publish_started'])->assertConflict();
    $this->postJson($report, ['claim' => $claim, 'stage' => 'container', 'container_id' => '456'])->assertOk();
    $this->postJson($report, ['claim' => $claim, 'stage' => 'publish_started'])->assertOk();
    $this->postJson($report, ['claim' => $claim, 'stage' => 'publish_started'])->assertConflict();
    $this->postJson($report, ['claim' => $claim, 'stage' => 'failed', 'error' => 'Timed out'])->assertOk();
    expect(DB::table('instagram_posts')->find($id)->status)->toBe('publishing');
    $this->deleteJson('/api/admin/instagram-queue/'.$id.'/schedule')->assertUnprocessable();
    $this->postJson('/api/internal/instagram/claim', ['account_id' => '123'])->assertJsonPath('data', null);
    $result = ['claim' => $claim, 'stage' => 'published', 'media_id' => '789', 'permalink' => 'https://www.instagram.com/p/Test/'];
    $this->postJson($report, $result)->assertOk(); $this->postJson($report, $result)->assertOk();
    expect(DB::table('instagram_posts')->find($id)->status)->toBe('published');
});
