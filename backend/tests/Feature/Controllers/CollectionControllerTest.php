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
            'icon' => 'test-icon',
            'type' => 'custom'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/admin/collections', $collectionData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'collection' => [
                    'id',
                    'name',
                    'description',
                    'icon',
                    'slug',
                    'type',
                    'created_at',
                    'updated_at'
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

        $collectionData = [
            'title' => 'Test Collection Name',
            'description' => 'Test Description',
            'icon' => 'test-icon',
            'type' => 'custom'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/admin/collections', $collectionData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'collection' => [
                    'slug'
                ]
            ])
            ->assertJson([
                'collection' => [
                    'slug' => 'test-collection-name'
                ]
            ]);
    }

    public function test_can_filter_collections_by_type()
    {
        // Clear existing collections
        Collection::query()->delete();
        
        // Create collections with specific types
        Collection::factory()->create(['type' => 'daily']);
        Collection::factory()->create(['type' => 'special']);
        Collection::factory()->create(['type' => 'custom']);

        $response = $this->getJson('/api/collections?type=daily');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'collections' => [
                    '*' => [
                        'id',
                        'name',
                        'slug',
                        'description',
                        'type'
                    ]
                ]
            ])
            ->assertJsonCount(1, 'collections')
            ->assertJsonPath('collections.0.type', 'daily');
    }

    public function test_admin_can_get_all_collections_with_transformed_data()
    {
        $this->user->update(['role' => 'admin']);
        
        // Clear existing collections
        Collection::query()->delete();
        
        // Create a collection with specific name
        $collection = Collection::factory()->create([
            'name' => 'Test Collection',
            'type' => 'daily'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/admin/collections');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'collections' => [
                    '*' => [
                        'id',
                        'name',
                        'slug',
                        'description',
                        'type'
                    ]
                ]
            ])
            ->assertJsonPath('collections.0.name', 'Test Collection');
    }

    public function test_admin_can_create_collection_with_adhkar()
    {
        $this->user->update(['role' => 'admin']);
        
        $adhkar = Adhkar::factory()->create();
        
        $collectionData = [
            'title' => 'Test Collection',
            'description' => 'Test Description',
            'icon' => 'test-icon',
            'type' => 'custom',
            'adhkarIds' => [$adhkar->id]
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/admin/collections', $collectionData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'collection' => [
                    'id',
                    'name',
                    'description',
                    'icon',
                    'slug',
                    'type',
                    'adhkar'
                ]
            ]);

        $this->assertDatabaseHas('adhkars', [
            'id' => $adhkar->id,
            'collection_id' => $response->json('collection.id')
        ]);
    }

    public function test_admin_can_update_collection_with_adhkar()
    {
        $this->user->update(['role' => 'admin']);
        
        $adhkar1 = Adhkar::factory()->create(['collection_id' => $this->collection->id]);
        $adhkar2 = Adhkar::factory()->create();
        
        $updateData = [
            'title' => 'Updated Collection',
            'description' => 'Updated Description',
            'icon' => 'updated-icon',
            'adhkarIds' => [$adhkar2->id]
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
                    'description',
                    'icon',
                    'adhkar'
                ]
            ]);

        $this->assertDatabaseHas('adhkars', [
            'id' => $adhkar1->id,
            'collection_id' => null
        ]);

        $this->assertDatabaseHas('adhkars', [
            'id' => $adhkar2->id,
            'collection_id' => $this->collection->id
        ]);
    }

    public function test_cannot_delete_collection_with_adhkar()
    {
        $this->user->update(['role' => 'admin']);
        
        Adhkar::factory()->create(['collection_id' => $this->collection->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson("/api/admin/collections/{$this->collection->id}");

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'نمی‌توان مجموعه حاوی اذکار را حذف کرد. ابتدا اذکار را حذف کنید.'
            ]);

        $this->assertDatabaseHas('collections', ['id' => $this->collection->id]);
    }

    public function test_handles_validation_error_in_admin_store()
    {
        $this->user->update(['role' => 'admin']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/admin/collections', [
            'title' => '',
            'type' => 'invalid_type'
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'errors' => [
                    'title',
                    'type'
                ]
            ]);
    }

    public function test_handles_validation_error_in_admin_update()
    {
        $this->user->update(['role' => 'admin']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson("/api/admin/collections/{$this->collection->id}", [
            'title' => '',
            'adhkarIds' => [99999] // Non-existent adhkar ID
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'errors' => [
                    'title',
                    'adhkarIds.0'
                ]
            ]);
    }
}