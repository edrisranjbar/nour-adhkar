<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('removes Instagram admin and worker access while preserving historical data', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin', 'active' => true]), 'admin');
    $id = DB::table('instagram_posts')->insertGetId([
        'verse_key' => '94:5', 'caption' => 'Retained archive', 'theme' => 'paper',
        'status' => 'scheduled', 'position' => 1, 'scheduled_at' => now()->subMinute(),
        'image_text' => 'Archived text', 'created_at' => now(), 'updated_at' => now(),
    ]);
    $this->get('/admin')->assertOk()->assertDontSee('صف اینستاگرام');
    foreach (['/admin/instagram-queue', '/admin/instagram-api/instagram-queue', '/api/admin/instagram-queue'] as $path) {
        $this->getJson($path)->assertNotFound();
    }
    foreach (['/admin/instagram-api/instagram-queue/1/schedule', '/api/admin/instagram-queue/1/schedule', '/api/internal/instagram/claim', '/api/internal/instagram/1/report'] as $path) {
        $this->postJson($path, [])->assertNotFound();
    }
    $this->assertDatabaseHas('instagram_posts', ['id' => $id, 'caption' => 'Retained archive', 'status' => 'scheduled']);
});
