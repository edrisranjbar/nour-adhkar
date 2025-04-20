<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\Post;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tymon\JWTAuth\Facades\JWTAuth;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;
    protected $post;
    protected $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->token = JWTAuth::fromUser($this->user);
        $this->post = Post::factory()->create([
            'user_id' => $this->user->id
        ]);
    }

    public function test_can_get_all_posts()
    {
        Post::factory()->count(3)->create([
            'status' => 'published',
            'published_at' => now()
        ]);

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
                        'categories'
                    ]
                ],
                'meta' => [
                    'current_page',
                    'total',
                    'per_page',
                    'last_page'
                ]
            ]);
    }

    public function test_can_get_post_by_slug()
    {
        $response = $this->getJson("/api/posts/{$this->post->slug}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'post' => [
                    'id',
                    'title',
                    'slug',
                    'content',
                    'excerpt',
                    'status',
                    'published_at'
                ]
            ]);
    }

    public function test_can_get_related_posts()
    {
        $category = Category::factory()->create();
        $post = Post::factory()->create([
            'status' => 'published',
            'published_at' => now()
        ]);
        $post->categories()->attach($category->id);
        
        Post::factory()->count(3)->create([
            'status' => 'published',
            'published_at' => now()
        ])->each(function ($post) use ($category) {
            $post->categories()->attach($category->id);
        });

        $response = $this->getJson("/api/posts/{$post->id}/related");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'related_posts' => [
                    '*' => [
                        'id',
                        'title',
                        'slug',
                        'excerpt',
                        'status',
                        'published_at'
                    ]
                ]
            ]);
    }

    public function test_admin_can_create_post()
    {
        $this->user->update(['role' => 'admin']);

        $postData = [
            'title' => 'Test Post',
            'content' => 'Test Content',
            'status' => 'draft',
            'published_at' => now()->addDay()
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/admin/posts', $postData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'post' => [
                    'id',
                    'title',
                    'slug',
                    'content',
                    'status',
                    'published_at'
                ]
            ]);
    }

    public function test_admin_can_update_post()
    {
        $this->user->update(['role' => 'admin']);

        $updateData = [
            'title' => 'Updated Post',
            'content' => 'Updated Content',
            'status' => 'published',
            'published_at' => now()
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson("/api/admin/posts/{$this->post->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'post' => [
                    'title' => 'Updated Post',
                    'content' => 'Updated Content',
                    'status' => 'published'
                ]
            ]);
    }

    public function test_admin_can_delete_post()
    {
        $this->user->update(['role' => 'admin']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson("/api/admin/posts/{$this->post->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'مقاله با موفقیت حذف شد']);

        $this->assertDatabaseMissing('posts', ['id' => $this->post->id]);
    }

    public function test_admin_can_upload_image()
    {
        $this->user->update(['role' => 'admin']);
        $file = UploadedFile::fake()->image('post.jpg');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson("/api/admin/posts/{$this->post->id}/featured-image", [
            'image' => $file
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'url'
            ]);
    }

    public function test_non_admin_cannot_create_post()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/admin/posts', [
            'title' => 'Test Post',
            'content' => 'Test Content'
        ]);

        $response->assertStatus(403);
    }

    public function test_validation_fails_for_invalid_post_data()
    {
        $this->user->update(['role' => 'admin']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/admin/posts', [
            'title' => '',
            'content' => '',
            'status' => ''
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'content', 'status']);
    }

    public function test_returns_404_for_non_existent_post()
    {
        $response = $this->getJson('/api/posts/non-existent-slug');
        $response->assertStatus(404);
    }

    public function test_post_slug_is_generated_automatically()
    {
        $this->user->update(['role' => 'admin']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/admin/posts', [
            'title' => 'Test Post Title',
            'content' => 'Test Content',
            'status' => 'draft',
            'published_at' => now()->addDay()
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'post' => [
                    'slug' => 'test-post-title'
                ]
            ]);
    }
} 