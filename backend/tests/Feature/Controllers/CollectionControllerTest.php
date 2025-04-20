<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\Collection;
use App\Models\Adhkar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\Facades\JWTAuth;

class CollectionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;
    protected $collection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->token = JWTAuth::fromUser($this->user);
        $this->collection = Collection::factory()->create();
    }

    public function test_can_get_all_collections()
    {
        Collection::factory()->count(3)->create();

        $response = $this->getJson('/api/collections');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'collections' => [
                    '*' => [
                        'id',
                        'name',
                        'slug',
                        'description'
                    ]
                ]
            ]);
    }

    public function test_can_get_collection_by_slug()
    {
        Adhkar::factory()->count(3)->create([
            'collection_id' => $this->collection->id
        ]);

        $response = $this->getJson("/api/collections/{$this->collection->slug}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'collection' => [
                    'id',
                    'name',
                    'slug',
                    'description'
                ]
            ]);
    }

    public function test_admin_can_create_collection()
    {
        $this->user->update(['role' => 'admin']);

        $collectionData = [
            'title' => 'Test Collection',
            'description' => 'Test Description',
            'type' => 'custom'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/admin/collections', $collectionData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'collection' => [
                    'id',
                    'name',
                    'slug',
                    'description',
                    'type'
                ]
            ]);
    }

    public function test_admin_can_update_collection()
    {
        $this->user->update(['role' => 'admin']);

        $updateData = [
            'title' => 'Updated Collection',
            'description' => 'Updated Description',
            'type' => 'custom'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson("/api/admin/collections/{$this->collection->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'collection' => [
                    'id',
                    'name',
                    'slug',
                    'description',
                    'type'
                ]
            ]);
    }

    public function test_admin_can_delete_collection()
    {
        $this->user->update(['role' => 'admin']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson("/api/admin/collections/{$this->collection->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'مجموعه با موفقیت حذف شد']);

        $this->assertDatabaseMissing('collections', ['id' => $this->collection->id]);
    }

    public function test_non_admin_cannot_create_collection()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/admin/collections', [
            'name' => 'Test Collection',
            'description' => 'Test Description'
        ]);

        $response->assertStatus(403);
    }

    public function test_validation_fails_for_invalid_collection_data()
    {
        $this->user->update(['role' => 'admin']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/admin/collections', [
            'title' => '',
            'description' => ''
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_returns_404_for_non_existent_collection()
    {
        $response = $this->getJson('/api/collections/non-existent-slug');
        $response->assertStatus(404);
    }

    public function test_collection_slug_is_generated_automatically()
    {
        $this->user->update(['role' => 'admin']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/admin/collections', [
            'title' => 'Test Collection Name',
            'description' => 'Test Description',
            'type' => 'custom'
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'collection' => [
                    'slug'
                ]
            ]);
    }
}