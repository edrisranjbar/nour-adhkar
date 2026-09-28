<?php

use App\Models\Post;
use App\Models\User;
use Database\Seeders\AppArticlesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('publishes the two app articles idempotently and serves them from the posts API', function () {
    User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => Hash::make('secret12'), 'role' => 'admin']);

    $this->seed(AppArticlesSeeder::class);
    $this->seed(AppArticlesSeeder::class);
    expect(Post::count())->toBe(2);

    $this->getJson('/api/posts')->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.slug', 'virtue-of-remembering-allah')
        ->assertJsonPath('data.1.slug', 'sayyid-al-istighfar');
});
