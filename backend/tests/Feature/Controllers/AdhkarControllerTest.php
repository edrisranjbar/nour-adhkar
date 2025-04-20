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
} 