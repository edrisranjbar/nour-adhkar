<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\Facades\JWTAuth;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;
    protected $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'admin']);
        $this->token = JWTAuth::fromUser($this->user);
        $this->category = Category::factory()->create();
    }

    public function test_can_get_all_categories()
    {
        Category::factory()->count(3)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/admin/categories');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'name',
                    'slug',
                    'description',
                    'parent_id',
                    'created_at',
                    'updated_at'
                ]
            ]);
    }

    public function test_can_create_category()
    {
        $categoryData = [
            'name' => 'Test Category',
            'description' => 'Test Description'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/admin/categories', $categoryData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'name',
                'slug',
                'description',
                'created_at',
                'updated_at'
            ])
            ->assertJson([
                'name' => 'Test Category',
                'description' => 'Test Description'
            ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Test Category',
            'description' => 'Test Description'
        ]);
    }

    public function test_validates_category_creation()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/admin/categories', [
            'name' => '', // Empty name
            'description' => 'Test Description'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_can_get_single_category()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson("/api/admin/categories/{$this->category->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'category' => [
                    'id',
                    'name',
                    'slug',
                    'description',
                    'parent_id',
                    'created_at',
                    'updated_at'
                ]
            ])
            ->assertJsonPath('category.id', $this->category->id);
    }

    public function test_returns_404_for_non_existent_category()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/admin/categories/99999');

        $response->assertStatus(404);
    }

    public function test_can_update_category()
    {
        $updateData = [
            'name' => 'Updated Category',
            'description' => 'Updated Description'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson("/api/admin/categories/{$this->category->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'slug',
                'description',
                'created_at',
                'updated_at'
            ])
            ->assertJson([
                'name' => 'Updated Category',
                'description' => 'Updated Description'
            ]);

        $this->assertDatabaseHas('categories', [
            'id' => $this->category->id,
            'name' => 'Updated Category',
            'description' => 'Updated Description'
        ]);
    }

    public function test_validates_category_update()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson("/api/admin/categories/{$this->category->id}", [
            'name' => '', // Empty name
            'description' => 'Updated Description'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_can_delete_category()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson("/api/admin/categories/{$this->category->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('categories', ['id' => $this->category->id]);
    }

    public function test_cannot_delete_category_with_children()
    {
        $childCategory = Category::factory()->create([
            'parent_id' => $this->category->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson("/api/admin/categories/{$this->category->id}");

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Cannot delete category with subcategories. Please delete subcategories first.'
            ]);

        $this->assertDatabaseHas('categories', ['id' => $this->category->id]);
    }

    public function test_can_create_category_with_parent()
    {
        $parentCategory = Category::factory()->create();

        $categoryData = [
            'name' => 'Child Category',
            'description' => 'Child Description',
            'parent_id' => $parentCategory->id
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/admin/categories', $categoryData);

        $response->assertStatus(201)
            ->assertJson([
                'name' => 'Child Category',
                'description' => 'Child Description',
                'parent_id' => $parentCategory->id
            ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Child Category',
            'parent_id' => $parentCategory->id
        ]);
    }

    public function test_validates_parent_category_exists()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/admin/categories', [
            'name' => 'Test Category',
            'description' => 'Test Description',
            'parent_id' => 99999 // Non-existent parent
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['parent_id']);
    }

    public function test_validates_category_cannot_be_own_parent()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson("/api/admin/categories/{$this->category->id}", [
            'name' => 'Test Category',
            'description' => 'Test Description',
            'parent_id' => $this->category->id
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['parent_id']);
    }
} 