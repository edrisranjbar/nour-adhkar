<?php

namespace Tests\Feature\Post;

use Tests\TestCase;
use App\Models\Post;
use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class PostAdminTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->token = Auth::login($this->admin);
    }

    public function test_admin_can_create_post()
    {
        $category = Category::factory()->create();
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->postJson('/api/admin/posts', [
            'title' => 'Test Post',
            'content' => 'Test content',
            'excerpt' => 'Test excerpt',
            'category_ids' => [$category->id],
            'status' => 'draft'
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'post' => [
                    'id',
                    'title',
                    'slug',
                    'content',
                    'excerpt',
                    'status',
                    'categories' => [
                        '*' => [
                            'id',
                            'name'
                        ]
                    ]
                ]
            ]);

        $this->assertDatabaseHas('posts', [
            'title' => 'Test Post',
            'status' => 'draft'
        ]);
    }

    public function test_admin_can_update_post()
    {
        $post = Post::factory()->create(['status' => 'draft']);
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->putJson("/api/admin/posts/{$post->id}", [
            'title' => 'Updated Title',
            'content' => 'Updated content',
            'status' => 'published'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('post.title', 'Updated Title')
            ->assertJsonPath('post.status', 'published');

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated Title',
            'status' => 'published'
        ]);
    }

    public function test_admin_can_delete_post()
    {
        $post = Post::factory()->create();
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->deleteJson("/api/admin/posts/{$post->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_admin_can_upload_featured_image()
    {
        Storage::fake('public');
        
        $file = UploadedFile::fake()->image('post.jpg');
        $post = Post::factory()->create();
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->postJson("/api/admin/posts/{$post->id}/featured-image", [
            'image' => $file
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'url'
            ]);

        Storage::disk('public')->assertExists('posts/' . $file->hashName());
    }

    public function test_non_admin_cannot_access_admin_endpoints()
    {
        $user = User::factory()->create(['role' => 'user']);
        $token = Auth::login($user);
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->getJson('/api/admin/posts');

        $response->assertStatus(403);
    }

    public function test_admin_can_get_all_posts_including_drafts()
    {
        Post::factory()->count(2)->create(['status' => 'published']);
        Post::factory()->count(3)->create(['status' => 'draft']);
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->getJson('/api/admin/posts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'posts' => [
                    'data' => [
                        '*' => [
                            'id',
                            'title',
                            'slug',
                            'content',
                            'excerpt',
                            'status',
                            'created_at',
                            'updated_at',
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
                    'current_page',
                    'total',
                    'per_page',
                    'last_page'
                ]
            ])
            ->assertJsonCount(5, 'posts.data');
    }
} 