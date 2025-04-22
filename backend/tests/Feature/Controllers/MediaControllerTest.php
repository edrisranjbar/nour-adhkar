<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;

class MediaControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;
    protected $media;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'admin']);
        $this->token = JWTAuth::fromUser($this->user);
        $this->media = Media::factory()->create();
        Storage::fake('public');
    }

    public function test_can_get_all_media()
    {
        Media::factory()->count(3)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/admin/media');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'path',
                            'url',
                            'type',
                            'size'
                        ]
                    ],
                    'current_page',
                    'per_page',
                    'total'
                ]
            ]);
    }

    public function test_can_filter_media_by_type()
    {
        Media::factory()->create(['type' => 'image/jpeg']);
        Media::factory()->create(['type' => 'audio/mpeg']);
        Media::factory()->create(['type' => 'application/pdf']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/admin/media?type=image');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.type', 'image/jpeg');
    }

    public function test_can_search_media()
    {
        Media::factory()->create(['name' => 'Test Image']);
        Media::factory()->create(['name' => 'Another File']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/admin/media?search=Test');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.name', 'Test Image');
    }

    public function test_can_get_single_media()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson("/api/admin/media/{$this->media->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'media' => [
                        'id',
                        'name',
                        'path',
                        'url',
                        'type',
                        'size'
                    ]
                ]
            ])
            ->assertJsonPath('data.media.id', $this->media->id);
    }

    public function test_returns_404_for_non_existent_media()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/admin/media/99999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'رسانه مورد نظر یافت نشد'
            ]);
    }

    public function test_can_upload_media()
    {
        $file = UploadedFile::fake()->image('test.jpg');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/admin/media/upload', [
            'files' => [$file]
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'media' => [
                        '*' => [
                            'id',
                            'name',
                            'path',
                            'url',
                            'type',
                            'size'
                        ]
                    ]
                ]
            ]);

        Storage::disk('public')->assertExists('uploads/media/' . basename($response->json('data.media.0.path')));
    }

    public function test_validates_file_upload()
    {
        $file = UploadedFile::fake()->create('test.txt', 30000); // 30MB file

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/admin/media/upload', [
            'files' => [$file]
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message'
            ]);
    }

    public function test_can_update_media()
    {
        $updateData = [
            'name' => 'Updated Name',
            'description' => 'Updated Description',
            'tags' => ['tag1', 'tag2']
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson("/api/admin/media/{$this->media->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'media' => [
                        'id',
                        'name',
                        'description',
                        'tags'
                    ]
                ]
            ])
            ->assertJsonPath('data.media.name', 'Updated Name')
            ->assertJsonPath('data.media.description', 'Updated Description')
            ->assertJsonPath('data.media.tags', ['tag1', 'tag2']);
    }

    public function test_can_delete_media()
    {
        Storage::disk('public')->put($this->media->path, 'test content');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson("/api/admin/media/{$this->media->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'فایل با موفقیت حذف شد'
            ]);

        $this->assertDatabaseMissing('media', ['id' => $this->media->id]);
        Storage::disk('public')->assertMissing($this->media->path);
    }

    public function test_can_delete_multiple_media()
    {
        $media1 = Media::factory()->create();
        $media2 = Media::factory()->create();
        
        Storage::disk('public')->put($media1->path, 'test content');
        Storage::disk('public')->put($media2->path, 'test content');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/admin/media/delete-multiple', [
            'ids' => [$media1->id, $media2->id]
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => '2 فایل با موفقیت حذف شد'
            ]);

        $this->assertDatabaseMissing('media', ['id' => $media1->id]);
        $this->assertDatabaseMissing('media', ['id' => $media2->id]);
        Storage::disk('public')->assertMissing($media1->path);
        Storage::disk('public')->assertMissing($media2->path);
    }

    public function test_handles_validation_error_in_delete_multiple()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/admin/media/delete-multiple', [
            'ids' => [99999] // Non-existent media ID
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message'
            ]);
    }

    public function test_handles_storage_error_in_delete()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson("/api/admin/media/{$this->media->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'فایل با موفقیت حذف شد'
            ]);

        $this->assertDatabaseMissing('media', ['id' => $this->media->id]);
    }
} 