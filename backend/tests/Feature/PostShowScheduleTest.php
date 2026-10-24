<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('hides a scheduled article on its direct API URL until publication time', function () {
    $id = DB::table('posts')->insertGetId([
        'user_id' => User::factory()->create()->id, 'title' => 'مقاله آینده', 'slug' => 'future-article',
        'content' => 'متن آینده', 'status' => 'published', 'published_at' => now()->addDays(2),
        'created_at' => now(), 'updated_at' => now(),
    ]);
    $this->getJson('/api/posts/future-article')->assertNotFound();
    $this->getJson('/api/posts')->assertJsonCount(0, 'data');
    $this->getJson('/api/app-articles')->assertJsonCount(0, 'data');
    expect(DB::table('posts')->where('id', $id)->value('views'))->toBe(0);
    $this->travel(2)->days();
    $this->getJson('/api/posts/future-article')->assertOk()->assertJsonPath('id', $id);
});

it('does not expose a draft or an article without a publication date', function () {
    foreach (['draft', 'undated'] as $slug) {
        DB::table('posts')->insert([
            'user_id' => User::factory()->create()->id, 'title' => $slug, 'slug' => $slug, 'content' => 'متن',
            'status' => $slug === 'draft' ? 'draft' : 'published', 'published_at' => $slug === 'draft' ? now()->subDay() : null,
        ]);
        $this->getJson('/api/posts/'.$slug)->assertNotFound();
    }
});
