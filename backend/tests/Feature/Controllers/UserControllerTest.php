<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use App\Services\BadgeService;
use Mockery;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;
    protected $badgeService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'heart_score' => 50,
            'streak' => 5,
            'completed_dates' => [now()->format('Y-m-d')],
            'total_dhikrs' => 10
        ]);
        
        $this->token = JWTAuth::fromUser($this->user);
        
        // Mock BadgeService
        $this->badgeService = Mockery::mock(BadgeService::class);
        $this->app->instance(BadgeService::class, $this->badgeService);
    }

    public function test_can_get_user_profile()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/user/profile');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'profile' => [
                    'id',
                    'name',
                    'email',
                    'avatar',
                    'heart_score',
                    'streak',
                    'has_new_badge',
                    'role',
                    'active',
                    'created_at',
                    'updated_at'
                ]
            ]);
    }

    public function test_handles_error_in_get_profile()
    {
        // Force an error by making the user not found
        $this->user->delete();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/user/profile');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.'
            ]);
    }

    public function test_can_update_user_profile()
    {
        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'password' => 'newpassword123'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson('/api/user/profile', $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'پروفایل با موفقیت به‌روزرسانی شد'
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ]);
    }

    public function test_handles_error_in_update_profile()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson('/api/user/profile', [
            'email' => 'invalid-email'
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'errors' => [
                    'email' => []
                ]
            ]);
    }

    public function test_can_update_user_name()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->patchJson('/api/user/name', [
            'name' => 'New Name'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'نام با موفقیت به‌روزرسانی شد',
                'user' => [
                    'name' => 'New Name'
                ]
            ]);
    }

    public function test_can_update_user_password()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->patchJson('/api/user/password', [
            'current_password' => 'password123',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'رمز عبور با موفقیت به‌روزرسانی شد'
            ]);

        $this->assertTrue(Hash::check('newpassword123', $this->user->fresh()->password));
    }

    public function test_handles_wrong_current_password()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->patchJson('/api/user/password', [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'رمز عبور فعلی اشتباه است'
            ]);
    }

    public function test_can_update_heart_score()
    {
        $this->badgeService->shouldReceive('checkAndAwardBadges')
            ->once()
            ->andReturn(true);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->patchJson('/api/user/heart', [
            'heart_score' => 75
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'امتیاز قلب با موفقیت به‌روزرسانی شد',
                'user' => [
                    'heart_score' => 75
                ],
                'badge_awarded' => true
            ]);
    }

    public function test_handles_error_in_update_heart_score()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->patchJson('/api/user/heart', [
            'heart_score' => 'invalid'
        ]);

        $response->assertStatus(500)
            ->assertJson([
                'message' => 'خطا در به‌روزرسانی امتیاز قلب'
            ]);
    }

    public function test_can_get_user_stats()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/user/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'today_count',
                'favorite_count',
                'streak',
                'heart_score',
                'total_dhikrs',
                'completed_dates'
            ])
            ->assertJson([
                'today_count' => 1,
                'favorite_count' => 1,
                'streak' => 1,
                'heart_score' => 50,
                'total_dhikrs' => 10
            ]);
    }

    public function test_handles_error_in_get_user_stats()
    {
        // Force an error by making the user not found
        $this->user->delete();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/user/stats');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.'
            ]);
    }

    public function test_can_update_user_avatar()
    {
        Storage::fake('public');
        
        $file = UploadedFile::fake()->image('avatar.jpg');
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->post('/api/user/avatar', [
            'avatar' => $file
        ], [
            'Content-Type' => 'multipart/form-data'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'تصویر پروفایل با موفقیت به‌روزرسانی شد'
            ]);

        Storage::disk('public')->assertExists('avatars/' . $file->hashName());
    }

    public function test_handles_error_in_update_avatar()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->post('/api/user/avatar', [], [
            'Content-Type' => 'multipart/form-data'
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'فایل تصویر پروفایل ارسال نشده است'
            ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
} 