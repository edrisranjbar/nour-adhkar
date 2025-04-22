<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\Adhkar;
use App\Models\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdhkarControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;
    protected $adhkar;
    protected $collection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->token = JWTAuth::fromUser($this->user);
        $this->collection = Collection::factory()->create();
        $this->adhkar = Adhkar::factory()->create([
            'collection_id' => $this->collection->id
        ]);
    }

    public function test_can_get_all_adhkar()
    {
        Adhkar::factory()->count(3)->create([
            'collection_id' => $this->collection->id
        ]);

        $response = $this->getJson('/api/adhkars');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'adhkar' => [
                    '*' => [
                        'id',
                        'arabic_text',
                        'translation',
                        'count',
                        'collection_id'
                    ]
                ]
            ]);
    }

    public function test_can_toggle_favorite()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson("/api/adhkar/favorites/{$this->adhkar->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'isFavorite',
                'message',
                'dhikr' => [
                    'id',
                    'arabic_text',
                    'translation',
                    'count'
                ]
            ]);
    }

    public function test_can_get_favorites()
    {
        $this->user->favorites = [$this->adhkar->id];
        $this->user->save();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/adhkar/favorites');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'favorites' => [
                    '*' => [
                        'id',
                        'arabic_text',
                        'translation',
                        'count'
                    ]
                ]
            ]);
    }

    public function test_admin_can_create_adhkar()
    {
        $this->user->update(['role' => 'admin']);

        $adhkarData = [
            'title' => 'Test Adhkar',
            'arabic_text' => 'Test Arabic Text',
            'translation' => 'Test Translation',
            'count' => 5,
            'collection_id' => 1
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/admin/adhkar', $adhkarData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'adhkar' => [
                    'id',
                    'title',
                    'arabic_text',
                    'translation',
                    'count',
                    'collection_id',
                    'created_at',
                    'updated_at'
                ]
            ]);
    }

    public function test_admin_can_update_adhkar()
    {
        $this->user->update(['role' => 'admin']);

        $updateData = [
            'arabic_text' => 'Updated Arabic Text',
            'translation' => 'Updated Translation',
            'count' => 5,
            'collection_id' => $this->collection->id
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson("/api/admin/adhkar/{$this->adhkar->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'ذکر با موفقیت به‌روزرسانی شد',
                'adhkar' => [
                    'arabic_text' => 'Updated Arabic Text',
                    'translation' => 'Updated Translation',
                    'count' => 5
                ]
            ]);
    }

    public function test_admin_can_delete_adhkar()
    {
        $this->user->update(['role' => 'admin']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson("/api/admin/adhkar/{$this->adhkar->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'ذکر با موفقیت حذف شد']);

        $this->assertDatabaseMissing('adhkars', ['id' => $this->adhkar->id]);
    }

    public function test_non_admin_cannot_create_adhkar()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/admin/adhkar', [
            'arabic_text' => 'Test Arabic Text',
            'translation' => 'Test Translation',
            'count' => 5,
            'collection_id' => $this->collection->id
        ]);

        $response->assertStatus(403);
    }

    public function test_validation_fails_for_invalid_adhkar_data()
    {
        $this->user->update(['role' => 'admin']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/admin/adhkar', [
            'arabic_text' => '',
            'translation' => '',
            'count' => '',
            'collection_id' => ''
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['arabic_text', 'translation', 'count', 'collection_id']);
    }

    public function test_handles_non_existent_collection_in_show()
    {
        $response = $this->getJson('/api/collections/non-existent');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'مجموعه مورد نظر یافت نشد'
            ]);
    }

    public function test_handles_error_in_get_favorites()
    {
        // Force an error by making the user's favorites property invalid
        $this->user->favorites = 'invalid';
        $this->user->save();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/adhkar/favorites');

        $response->assertStatus(500)
            ->assertJsonStructure([
                'message',
                'exception',
                'file',
                'line'
            ]);
    }

    public function test_handles_non_existent_adhkar_in_toggle_favorite()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/adhkar/favorites/99999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'ذکر مورد نظر یافت نشد'
            ]);
    }

    public function test_handles_error_in_toggle_favorite()
    {
        // Force an error by making the user's favorites property invalid
        $this->user->favorites = 'invalid';
        $this->user->save();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson("/api/adhkar/favorites/{$this->adhkar->id}");

        $response->assertStatus(500)
            ->assertJsonStructure([
                'message',
                'exception',
                'file',
                'line'
            ]);
    }

    public function test_handles_non_existent_adhkar_in_admin_show()
    {
        $this->user->update(['role' => 'admin']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/admin/adhkar/99999');

        $response->assertStatus(404);
    }

    public function test_handles_non_existent_adhkar_in_admin_update()
    {
        $this->user->update(['role' => 'admin']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson('/api/admin/adhkar/99999', [
            'arabic_text' => 'Test Arabic Text',
            'translation' => 'Test Translation',
            'count' => 5,
            'collection_id' => $this->collection->id
        ]);

        $response->assertStatus(404);
    }

    public function test_handles_non_existent_adhkar_in_admin_delete()
    {
        $this->user->update(['role' => 'admin']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson('/api/admin/adhkar/99999');

        $response->assertStatus(404);
    }

    public function test_handles_validation_error_in_admin_store()
    {
        $this->user->update(['role' => 'admin']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/admin/adhkar', [
            'arabic_text' => '',
            'translation' => '',
            'count' => '',
            'collection_id' => ''
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['arabic_text', 'translation', 'count', 'collection_id']);
    }

    public function test_handles_validation_error_in_admin_update()
    {
        $this->user->update(['role' => 'admin']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson("/api/admin/adhkar/{$this->adhkar->id}", [
            'arabic_text' => '',
            'translation' => '',
            'count' => '',
            'collection_id' => ''
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['arabic_text', 'translation', 'count', 'collection_id']);
    }

    public function test_handles_empty_adhkar_list()
    {
        // Delete all adhkar
        Adhkar::query()->delete();

        $response = $this->getJson('/api/adhkars');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'adhkar' => []
            ]);
    }

    public function test_handles_empty_collection_adhkar()
    {
        // Create a new collection without any adhkar
        $emptyCollection = Collection::factory()->create();

        $response = $this->getJson("/api/collections/{$emptyCollection->slug}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'collection' => [
                    'name' => $emptyCollection->name,
                    'adhkar' => []
                ]
            ]);
    }

    public function test_handles_invalid_collection_id_in_admin_store()
    {
        $this->user->update(['role' => 'admin']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/admin/adhkar', [
            'arabic_text' => 'Test Arabic Text',
            'translation' => 'Test Translation',
            'count' => 5,
            'collection_id' => 99999 // Non-existent collection ID
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['collection_id']);
    }

    public function test_handles_invalid_collection_id_in_admin_update()
    {
        $this->user->update(['role' => 'admin']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson("/api/admin/adhkar/{$this->adhkar->id}", [
            'arabic_text' => 'Test Arabic Text',
            'translation' => 'Test Translation',
            'count' => 5,
            'collection_id' => 99999 // Non-existent collection ID
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['collection_id']);
    }

    public function test_admin_can_get_all_adhkar_with_collections()
    {
        $this->user->update(['role' => 'admin']);
        
        // Clear existing data
        Adhkar::query()->delete();
        Collection::query()->delete();
        
        $collection = Collection::factory()->create(['name' => 'Test Collection']);
        $adhkar = Adhkar::factory()->create([
            'collection_id' => $collection->id,
            'title' => 'Test Adhkar',
            'arabic_text' => 'Test Arabic Text',
            'translation' => 'Test Translation',
            'count' => 5
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/admin/adhkar');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'adhkar' => [
                    '*' => [
                        'id',
                        'title',
                        'arabic_text',
                        'translation',
                        'count',
                        'collection' => [
                            'id',
                            'name',
                            'title'
                        ]
                    ]
                ]
            ])
            ->assertJson([
                'success' => true,
                'adhkar' => [
                    [
                        'id' => $adhkar->id,
                        'title' => 'Test Adhkar',
                        'collection' => [
                            'id' => $collection->id,
                            'name' => 'Test Collection',
                            'title' => 'Test Collection'
                        ]
                    ]
                ]
            ]);
    }

    public function test_admin_can_get_single_adhkar_with_collection()
    {
        $this->user->update(['role' => 'admin']);
        
        $collection = Collection::factory()->create(['name' => 'Test Collection']);
        $adhkar = Adhkar::factory()->create([
            'collection_id' => $collection->id,
            'title' => 'Test Adhkar',
            'arabic_text' => 'Test Arabic Text',
            'translation' => 'Test Translation',
            'count' => 5
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson("/api/admin/adhkar/{$adhkar->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'adhkar' => [
                    'id',
                    'title',
                    'arabic_text',
                    'translation',
                    'count',
                    'collection' => [
                        'id',
                        'name',
                        'title'
                    ]
                ]
            ])
            ->assertJson([
                'success' => true,
                'adhkar' => [
                    'id' => $adhkar->id,
                    'title' => 'Test Adhkar',
                    'collection' => [
                        'id' => $collection->id,
                        'name' => 'Test Collection',
                        'title' => 'Test Collection'
                    ]
                ]
            ]);
    }

    public function test_admin_can_create_adhkar_with_optional_fields()
    {
        $this->user->update(['role' => 'admin']);

        $adhkarData = [
            'title' => 'Test Adhkar',
            'prefix' => 'Test Prefix',
            'arabic_text' => 'Test Arabic Text',
            'translation' => 'Test Translation',
            'count' => 5,
            'collection_id' => $this->collection->id
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/admin/adhkar', $adhkarData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'adhkar' => [
                    'id',
                    'title',
                    'prefix',
                    'arabic_text',
                    'translation',
                    'count',
                    'collection_id'
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'ذکر با موفقیت ایجاد شد',
                'adhkar' => [
                    'title' => 'Test Adhkar',
                    'prefix' => 'Test Prefix',
                    'arabic_text' => 'Test Arabic Text',
                    'translation' => 'Test Translation',
                    'count' => 5,
                    'collection_id' => $this->collection->id
                ]
            ]);
    }

    public function test_handles_non_existent_collection_in_show_with_null_check()
    {
        // Delete the collection to ensure it doesn't exist
        $this->collection->delete();

        $response = $this->getJson("/api/collections/{$this->collection->slug}");

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'مجموعه مورد نظر یافت نشد'
            ]);
    }

    public function test_handles_error_in_get_favorites_with_empty_array()
    {
        // Set favorites to empty array
        $this->user->favorites = [];
        $this->user->save();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/adhkar/favorites');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'favorites' => []
            ]);
    }

    public function test_handles_error_in_toggle_favorite_with_empty_favorites()
    {
        // Set favorites to empty array
        $this->user->favorites = [];
        $this->user->save();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson("/api/adhkar/favorites/{$this->adhkar->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'isFavorite' => true,
                'message' => 'به لیست مورد علاقه اضافه شد'
            ]);
    }

    public function test_admin_index_handles_empty_collection()
    {
        $this->user->update(['role' => 'admin']);
        
        // Create an adhkar without a collection
        $adhkar = Adhkar::factory()->create([
            'collection_id' => null
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/admin/adhkar');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ])
            ->assertJsonStructure([
                'success',
                'adhkar' => [
                    '*' => [
                        'id',
                        'collection'
                    ]
                ]
            ]);
    }

    public function test_show_handles_collection_without_adhkar()
    {
        // Create a collection without any adhkar
        $collection = Collection::factory()->create();

        $response = $this->getJson("/api/collections/{$collection->slug}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'collection' => [
                    'name' => $collection->name,
                    'adhkar' => []
                ]
            ]);
    }
} 