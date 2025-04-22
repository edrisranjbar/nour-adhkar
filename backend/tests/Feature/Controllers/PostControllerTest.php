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
use Illuminate\Foundation\Testing\WithFaker;

class PostControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $user;
    protected $category;
    protected $post;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        // Create admin user
        $this->admin = User::factory()->create(['role' => 'admin']);
        
        // Create regular user
        $this->user = User::factory()->create();
        
        // Create a category
        $this->category = Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'description' => 'Test Description'
        ]);
        
        // Create a post
        $this->post = Post::create([
            'title' => 'Test Post',
            'slug' => 'test-post',
            'content' => 'Test Content',
            'excerpt' => 'Test Excerpt',
            'status' => 'published',
            'published_at' => now(),
            'user_id' => $this->admin->id
        ]);

        $this->post->categories()->attach($this->category->id);
    }

    public function test_can_get_all_posts()
    {
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
                                'slug',
                                'description'
                            ]
                        ]
                    ]
                ],
                'success',
                'meta' => [
                    'current_page',
                    'total',
                    'per_page',
                    'last_page'
                ]
            ]);
    }

    public function test_can_filter_posts_by_category()
    {
        $response = $this->getJson('/api/posts?category=' . $this->category->slug);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'success',
                'meta'
            ])
            ->assertJsonPath('data.0.categories.0.slug', $this->category->slug);
    }

    public function test_can_search_posts()
    {
        $response = $this->getJson('/api/posts?search=Test');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'success',
                'meta'
            ])
            ->assertJsonPath('data.0.title', 'Test Post');
    }

    public function test_returns_empty_posts_when_no_matches()
    {
        $response = $this->getJson('/api/posts?search=nonexistent');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [],
                'success' => true,
                'meta' => [
                    'current_page' => 1,
                    'total' => 0,
                    'per_page' => 10,
                    'last_page' => 1
                ]
            ]);
    }

    public function test_can_get_post_by_slug()
    {
        $response = $this->getJson('/api/posts/' . $this->post->slug);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'post' => [
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
                            'slug',
                            'description'
                        ]
                    ]
                ]
            ]);
    }

    public function test_returns_404_for_non_existent_post()
    {
        $response = $this->getJson('/api/posts/non-existent-slug');
        $response->assertStatus(404);
    }

    public function test_admin_can_create_post()
    {
        $token = JWTAuth::fromUser($this->admin);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/admin/posts', [
            'title' => 'New Post',
            'content' => 'New Content',
            'excerpt' => 'New Excerpt',
            'status' => 'published',
            'category_ids' => [$this->category->id]
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
                            'slug',
                            'description'
                        ]
                    ]
                ]
            ]);
    }

    public function test_admin_can_create_post_with_custom_slug()
    {
        $token = JWTAuth::fromUser($this->admin);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/admin/posts', [
            'title' => 'New Post',
            'slug' => 'custom-slug',
            'content' => 'New Content',
            'excerpt' => 'New Excerpt',
            'status' => 'published',
            'category_ids' => [$this->category->id]
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('post.slug', 'custom-slug');
    }

    public function test_generates_unique_slug_when_duplicate()
    {
        $token = JWTAuth::fromUser($this->admin);

        // Create first post
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/admin/posts', [
            'title' => 'Duplicate Post',
            'content' => 'Content',
            'status' => 'published',
            'category_ids' => [$this->category->id]
        ]);

        // Try to create second post with same title
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/admin/posts', [
            'title' => 'Duplicate Post',
            'content' => 'Content',
            'status' => 'published',
            'category_ids' => [$this->category->id]
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('post.slug', 'duplicate-post-2');
    }

    public function test_sets_published_at_when_status_is_published()
    {
        $token = JWTAuth::fromUser($this->admin);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/admin/posts', [
            'title' => 'New Post',
            'content' => 'New Content',
            'status' => 'published',
            'category_ids' => [$this->category->id]
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'post' => [
                    'published_at'
                ]
            ]);
    }

    public function test_cannot_create_post_with_invalid_data()
    {
        $token = JWTAuth::fromUser($this->admin);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/admin/posts', [
            'title' => '',
            'content' => '',
            'status' => 'invalid'
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'errors' => [
                    'title',
                    'content',
                    'status'
                ]
            ]);
    }

    public function test_admin_can_update_post()
    {
        $token = JWTAuth::fromUser($this->admin);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->putJson('/api/admin/posts/' . $this->post->id, [
            'title' => 'Updated Post',
            'content' => 'Updated Content',
            'excerpt' => 'Updated Excerpt',
            'status' => 'published',
            'category_ids' => [$this->category->id]
        ]);

        $response->assertStatus(200)
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
                            'slug',
                            'description'
                        ]
                    ]
                ]
            ]);
    }

    public function test_admin_can_update_post_with_new_slug()
    {
        $token = JWTAuth::fromUser($this->admin);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->putJson('/api/admin/posts/' . $this->post->id, [
            'title' => 'Updated Post',
            'slug' => 'new-custom-slug',
            'content' => 'Updated Content',
            'excerpt' => 'Updated Excerpt',
            'status' => 'published',
            'category_ids' => [$this->category->id]
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('post.slug', 'new-custom-slug');
    }

    public function test_generates_unique_slug_on_title_update()
    {
        $token = JWTAuth::fromUser($this->admin);

        // Create another post with similar title
        $otherPost = Post::create([
            'title' => 'Similar Post',
            'slug' => 'similar-post',
            'content' => 'Content',
            'status' => 'published',
            'published_at' => now(),
            'user_id' => $this->admin->id
        ]);

        // Update first post's title to match
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->putJson('/api/admin/posts/' . $this->post->id, [
            'title' => 'Similar Post',
            'content' => 'Updated Content',
            'status' => 'published',
            'category_ids' => [$this->category->id]
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('post.slug', 'similar-post-1');
    }

    public function test_cannot_update_post_with_invalid_data()
    {
        $token = JWTAuth::fromUser($this->admin);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->putJson('/api/admin/posts/' . $this->post->id, [
            'title' => '',
            'content' => '',
            'status' => 'invalid'
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'errors' => [
                    'title',
                    'content',
                    'status'
                ]
            ]);
    }

    public function test_admin_can_delete_post()
    {
        $token = JWTAuth::fromUser($this->admin);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->deleteJson('/api/admin/posts/' . $this->post->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'مقاله با موفقیت حذف شد'
            ]);

        $this->assertDatabaseMissing('posts', ['id' => $this->post->id]);
    }

    public function test_admin_can_upload_featured_image()
    {
        $token = JWTAuth::fromUser($this->admin);
        $file = UploadedFile::fake()->image('post.jpg');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/admin/posts/' . $this->post->id . '/featured-image', [
            'image' => $file
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'url'
            ]);

        Storage::disk('public')->assertExists('posts/' . $file->hashName());
    }

    public function test_validates_file_upload()
    {
        $token = JWTAuth::fromUser($this->admin);
        $file = UploadedFile::fake()->create('document.txt', 100);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/admin/posts/upload', [
            'file' => $file
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'errors' => [
                    'file'
                ]
            ]);
    }

    public function test_handles_file_upload_error()
    {
        $token = JWTAuth::fromUser($this->admin);
        $file = UploadedFile::fake()->image('test.jpg');

        // Simulate storage error
        Storage::shouldReceive('disk')
            ->once()
            ->with('public')
            ->andThrow(new \Exception('Storage error'));

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/admin/posts/upload', [
            'file' => $file
        ]);

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'message' => 'File upload failed'
            ]);
    }

    public function test_can_get_related_posts()
    {
        // Create another post in the same category
        $relatedPost = Post::create([
            'title' => 'Related Post',
            'slug' => 'related-post',
            'content' => 'Related Content',
            'excerpt' => 'Related Excerpt',
            'status' => 'published',
            'published_at' => now(),
            'user_id' => $this->admin->id
        ]);
        $relatedPost->categories()->attach($this->category->id);

        $response = $this->getJson('/api/posts/' . $this->post->id . '/related');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'related_posts' => [
                    '*' => [
                        'id',
                        'title',
                        'slug',
                        'content',
                        'excerpt',
                        'status',
                        'published_at'
                    ]
                ]
            ]);
    }

    public function test_admin_can_get_all_posts()
    {
        $token = JWTAuth::fromUser($this->admin);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
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
                                    'slug',
                                    'description'
                                ]
                            ]
                        ]
                    ],
                    'current_page',
                    'total',
                    'per_page',
                    'last_page'
                ]
            ]);
    }

    public function test_regular_user_cannot_access_admin_routes()
    {
        $token = JWTAuth::fromUser($this->user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/admin/posts');

        $response->assertStatus(403);
    }

    public function test_handles_error_in_upload_featured_image()
    {
        $token = JWTAuth::fromUser($this->admin);
        $file = UploadedFile::fake()->image('post.jpg');

        // Simulate storage error
        Storage::shouldReceive('disk')
            ->once()
            ->with('public')
            ->andThrow(new \Exception('Storage error'));

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/admin/posts/' . $this->post->id . '/featured-image', [
            'image' => $file
        ]);

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'message' => 'Featured image upload failed'
            ]);
    }

    public function test_handles_validation_error_in_upload_featured_image()
    {
        $token = JWTAuth::fromUser($this->admin);
        $file = UploadedFile::fake()->create('document.txt', 100);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/admin/posts/' . $this->post->id . '/featured-image', [
            'image' => $file
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'errors' => [
                    'image'
                ]
            ]);
    }

    public function test_handles_missing_image_in_upload_featured_image()
    {
        $token = JWTAuth::fromUser($this->admin);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/admin/posts/' . $this->post->id . '/featured-image', []);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'errors' => [
                    'image'
                ]
            ]);
    }

    public function test_handles_error_in_upload_file()
    {
        $token = JWTAuth::fromUser($this->admin);
        $file = UploadedFile::fake()->image('test.jpg');

        // Simulate storage error
        Storage::shouldReceive('disk')
            ->once()
            ->with('public')
            ->andThrow(new \Exception('Storage error'));

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/admin/posts/upload', [
            'file' => $file
        ]);

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'message' => 'File upload failed'
            ]);
    }

    public function test_handles_missing_file_in_upload_file()
    {
        $token = JWTAuth::fromUser($this->admin);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/admin/posts/upload', []);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'errors' => [
                    'file'
                ]
            ]);
    }
} 