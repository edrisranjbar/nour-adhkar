<?php

namespace Tests\Feature\Post;

use Tests\TestCase;
use App\Models\Post;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PostListTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_post_list()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $posts = Post::factory()
            ->count(3)
            ->for($user)
            ->create(['status' => 'published'])
            ->each(function ($post) use ($category) {
                $post->categories()->attach($category->id);
            });

        $response = $this->getJson('/api/posts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'slug',
                        'content',
                        'excerpt',
                        'status',
                        'published_at',
                        'user' => [
                            'id',
                            'name',
                            'avatar'
                        ],
                        'categories' => [
                            '*' => [
                                'id',
                                'name',
                                'slug'
                            ]
                        ]
                    ]
                ],
                'meta' => [
                    'current_page',
                    'total',
                    'per_page',
                    'last_page'
                ]
            ])
            ->assertJsonCount(3, 'data');
    }

    public function test_can_filter_posts_by_category()
    {
        $user = User::factory()->create();
        $category1 = Category::factory()->create(['name' => 'Technology', 'slug' => 'technology']);
        $category2 = Category::factory()->create(['name' => 'Religion', 'slug' => 'religion']);
        
        Post::factory()
            ->count(2)
            ->for($user)
            ->create(['status' => 'published'])
            ->each(function ($post) use ($category1) {
                $post->categories()->attach($category1->id);
            });

        Post::factory()
            ->count(3)
            ->for($user)
            ->create(['status' => 'published'])
            ->each(function ($post) use ($category2) {
                $post->categories()->attach($category2->id);
            });

        $response = $this->getJson('/api/posts?category=technology');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.categories.0.slug', 'technology');
    }

    public function test_can_search_posts()
    {
        $user = User::factory()->create();
        $post1 = Post::factory()
            ->for($user)
            ->create([
                'title' => 'Laravel Best Practices',
                'content' => 'This is about Laravel',
                'status' => 'published'
            ]);
        $post2 = Post::factory()
            ->for($user)
            ->create([
                'title' => 'Vue.js Tutorial',
                'content' => 'Learn Vue.js',
                'status' => 'published'
            ]);
        $post3 = Post::factory()
            ->for($user)
            ->create([
                'title' => 'Islamic Articles',
                'content' => 'About Islam',
                'status' => 'published'
            ]);

        $response = $this->getJson('/api/posts?search=laravel');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $post1->id);
    }

    public function test_only_published_posts_are_shown()
    {
        $user = User::factory()->create();
        Post::factory()
            ->for($user)
            ->create(['status' => 'draft']);
        Post::factory()
            ->count(2)
            ->for($user)
            ->create(['status' => 'published']);

        $response = $this->getJson('/api/posts');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_posts_are_paginated()
    {
        $user = User::factory()->create();
        Post::factory()
            ->count(15)
            ->for($user)
            ->create(['status' => 'published']);

        $response = $this->getJson('/api/posts?page=1&per_page=10');

        $response->assertStatus(200)
            ->assertJsonCount(10, 'data')
            ->assertJsonStructure([
                'meta' => [
                    'current_page',
                    'total',
                    'per_page',
                    'last_page'
                ]
            ])
            ->assertJsonPath('meta.total', 15)
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.last_page', 2);
    }

    public function test_posts_are_sorted_by_published_at()
    {
        $user = User::factory()->create();
        $oldPost = Post::factory()
            ->for($user)
            ->create([
                'status' => 'published',
                'published_at' => now()->subDays(2)
            ]);
        $newPost = Post::factory()
            ->for($user)
            ->create([
                'status' => 'published',
                'published_at' => now()
            ]);

        $response = $this->getJson('/api/posts');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.id', $newPost->id)
            ->assertJsonPath('data.1.id', $oldPost->id);
    }

    public function test_invalid_category_returns_empty_results()
    {
        $user = User::factory()->create();
        Post::factory()
            ->count(3)
            ->for($user)
            ->create(['status' => 'published']);

        $response = $this->getJson('/api/posts?category=non-existent');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }
} 