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

    public function test_post_belongs_to_category()
    {
        $category = Category::factory()->create();
        $post = Post::factory()->create();
        $post->categories()->attach($category->id);

        $this->assertInstanceOf(Category::class, $post->categories->first());
        $this->assertEquals($category->id, $post->categories->first()->id);
    }

    public function test_post_has_required_fields()
    {
        $post = Post::factory()->create([
            'title' => 'Test Post',
            'content' => 'Test content',
            'status' => 'draft'
        ]);

        $this->assertEquals('Test Post', $post->title);
        $this->assertEquals('Test content', $post->content);
        $this->assertEquals('draft', $post->status);
    }

    public function test_post_has_default_values()
    {
        $post = Post::factory()->create();

        $this->assertEquals('draft', $post->status);
        $this->assertNotNull($post->published_at);
    }

    public function test_post_factory_creates_valid_post()
    {
        $post = Post::factory()
            ->has(Category::factory())
            ->create();

        $this->assertInstanceOf(Post::class, $post);
        $this->assertNotNull($post->id);
        $this->assertNotEmpty($post->categories);
    }
} 