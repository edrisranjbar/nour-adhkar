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

    public function test_slug_is_generated_automatically()
    {
        $category = Category::create([
            'name' => 'Test Category Name',
            'description' => 'Test description'
        ]);

        $this->assertEquals('test-category-name', $category->slug);
    }

    public function test_slug_is_not_overwritten_if_provided()
    {
        $category = Category::create([
            'name' => 'Test Category Name',
            'slug' => 'custom-slug',
            'description' => 'Test description'
        ]);

        $this->assertEquals('custom-slug', $category->slug);
    }

    public function test_category_can_have_parent()
    {
        $parent = Category::factory()->create();
        $child = Category::factory()->create(['parent_id' => $parent->id]);

        $this->assertEquals($parent->id, $child->parent->id);
        $this->assertInstanceOf(Category::class, $child->parent);
    }

    public function test_category_can_have_children()
    {
        $parent = Category::factory()->create();
        $children = Category::factory()->count(3)->create(['parent_id' => $parent->id]);

        $this->assertCount(3, $parent->children);
        $this->assertInstanceOf(Category::class, $parent->children->first());
    }

    public function test_all_posts_includes_child_category_posts()
    {
        $parent = Category::factory()->create();
        $child = Category::factory()->create(['parent_id' => $parent->id]);
        
        // Create posts for parent category
        $parentPosts = Post::factory()->count(2)->create();
        foreach ($parentPosts as $post) {
            $post->categories()->attach($parent->id);
        }
        
        // Create posts for child category
        $childPosts = Post::factory()->count(3)->create();
        foreach ($childPosts as $post) {
            $post->categories()->attach($child->id);
        }

        $allPosts = $parent->allPosts()->get();
        
        $this->assertCount(5, $allPosts);
        $this->assertInstanceOf(Post::class, $allPosts->first());
    }
} 