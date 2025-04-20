<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_has_required_fields()
    {
        $category = Category::factory()->create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'description' => 'Test description'
        ]);

        $this->assertEquals('Test Category', $category->name);
        $this->assertEquals('test-category', $category->slug);
        $this->assertEquals('Test description', $category->description);
    }

    public function test_category_has_default_values()
    {
        $category = Category::factory()->create();

        $this->assertNotNull($category->name);
        $this->assertNotNull($category->slug);
        $this->assertNotNull($category->description);
    }

    public function test_category_can_have_posts()
    {
        $category = Category::factory()->create();
        $posts = Post::factory()->count(3)->create();
        
        // Attach the posts to the category
        foreach ($posts as $post) {
            $post->categories()->attach($category->id);
        }

        $this->assertCount(3, $category->posts);
        $this->assertInstanceOf(Post::class, $category->posts->first());
    }

    public function test_category_factory_creates_valid_category()
    {
        $category = Category::factory()->create();

        $this->assertInstanceOf(Category::class, $category);
        $this->assertNotNull($category->id);
    }
} 