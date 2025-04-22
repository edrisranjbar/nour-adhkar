<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Post;
use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_has_required_fields()
    {
        $user = User::factory()->create();
        $post = Post::create([
            'title' => 'Test Post',
            'content' => 'Test Content',
            'excerpt' => 'Test Excerpt',
            'featured_image' => 'test.jpg',
            'published_at' => now(),
            'user_id' => $user->id,
            'status' => 'published'
        ]);

        $this->assertEquals('Test Post', $post->title);
        $this->assertEquals('Test Content', $post->content);
        $this->assertEquals('Test Excerpt', $post->excerpt);
        $this->assertEquals('test.jpg', $post->featured_image);
        $this->assertEquals($user->id, $post->user_id);
        $this->assertEquals('published', $post->status);
    }

    public function test_post_has_default_values()
    {
        $user = User::factory()->create();
        $post = Post::create([
            'title' => 'Test Post',
            'content' => 'Test Content',
            'user_id' => $user->id
        ]);

        $this->assertEquals('draft', $post->status);
        $this->assertNull($post->published_at);
    }

    public function test_post_belongs_to_user()
    {
        $user = User::factory()->create();
        $post = Post::create([
            'title' => 'Test Post',
            'content' => 'Test Content',
            'user_id' => $user->id
        ]);

        $this->assertInstanceOf(User::class, $post->user);
        $this->assertEquals($user->id, $post->user->id);
    }

    public function test_post_can_have_categories()
    {
        $user = User::factory()->create();
        $post = Post::create([
            'title' => 'Test Post',
            'content' => 'Test Content',
            'user_id' => $user->id
        ]);

        $categories = Category::factory()->count(3)->create();
        
        $post->categories()->attach($categories->pluck('id'));

        $this->assertCount(3, $post->fresh()->categories);
        $this->assertInstanceOf(Category::class, $post->categories->first());
    }

    public function test_post_factory_creates_valid_post()
    {
        $post = Post::factory()->create();

        $this->assertNotNull($post->title);
        $this->assertNotNull($post->content);
        $this->assertNotNull($post->user_id);
        $this->assertNotNull($post->status);
    }

    public function test_published_scope_returns_only_published_posts()
    {
        $user = User::factory()->create();
        
        Post::create([
            'title' => 'Draft Post',
            'content' => 'Draft Content',
            'user_id' => $user->id,
            'status' => 'draft'
        ]);

        Post::create([
            'title' => 'Published Post',
            'content' => 'Published Content',
            'user_id' => $user->id,
            'status' => 'published',
            'published_at' => now()
        ]);

        $this->assertCount(1, Post::published()->get());
    }

    public function test_published_at_is_cast_to_datetime()
    {
        $user = User::factory()->create();
        $post = Post::create([
            'title' => 'Test Post',
            'content' => 'Test Content',
            'user_id' => $user->id,
            'published_at' => now()
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $post->published_at);
    }

    public function test_fillable_attributes()
    {
        $post = new Post();
        
        $this->assertTrue(in_array('title', $post->getFillable()));
        $this->assertTrue(in_array('slug', $post->getFillable()));
        $this->assertTrue(in_array('content', $post->getFillable()));
        $this->assertTrue(in_array('featured_image', $post->getFillable()));
        $this->assertTrue(in_array('excerpt', $post->getFillable()));
        $this->assertTrue(in_array('published_at', $post->getFillable()));
        $this->assertTrue(in_array('user_id', $post->getFillable()));
        $this->assertTrue(in_array('status', $post->getFillable()));
    }

    public function test_slug_is_generated_automatically()
    {
        $user = User::factory()->create();
        $post = Post::create([
            'title' => 'Test Post Title',
            'content' => 'Test Content',
            'user_id' => $user->id
        ]);

        $this->assertEquals('test-post-title', $post->slug);
    }

    public function test_slug_is_not_overwritten_if_provided()
    {
        $user = User::factory()->create();
        $post = Post::create([
            'title' => 'Test Post Title',
            'slug' => 'custom-slug',
            'content' => 'Test Content',
            'user_id' => $user->id
        ]);

        $this->assertEquals('custom-slug', $post->slug);
    }

    public function test_slug_is_updated_when_title_changes()
    {
        $user = User::factory()->create();
        $post = Post::create([
            'title' => 'Original Title',
            'content' => 'Test Content',
            'user_id' => $user->id
        ]);

        $post->update(['title' => 'Updated Title']);

        $this->assertEquals('updated-title', $post->fresh()->slug);
    }

    public function test_generate_unique_slug()
    {
        $user = User::factory()->create();
        
        // Create first post
        $post1 = Post::create([
            'title' => 'Test Post',
            'content' => 'Test Content',
            'user_id' => $user->id
        ]);

        // Create second post with same title
        $post2 = Post::create([
            'title' => 'Test Post',
            'content' => 'Test Content',
            'user_id' => $user->id
        ]);

        $this->assertEquals('test-post', $post1->slug);
        $this->assertEquals('test-post-1', $post2->slug);
    }
} 