<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->token = JWTAuth::fromUser($this->user);
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

    public function test_can_update_user_profile()
    {
        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson('/api/user/profile', $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'پروفایل با موفقیت به‌روزرسانی شد',
                'profile' => [
                    'name' => 'Updated Name',
                    'email' => 'updated@example.com'
                ]
            ]);
    }

    public function test_can_update_user_avatar()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/user/avatar', [
            'avatar' => $file
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'avatar_url',
                'user' => [
                    'avatar'
                ]
            ]);

        Storage::disk('public')->assertExists('avatars/' . $file->hashName());
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
            'current_password' => 'password',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword'
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'رمز عبور با موفقیت به‌روزرسانی شد']);
    }

    public function test_can_update_heart_score()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->patchJson('/api/user/heart', [
            'heart_score' => 50
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'user' => [
                    'heart_score' => 50
                ]
            ]);
    }

    public function test_can_get_user_stats()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/user/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_dhikrs',
                'streak',
                'completed_dates'
            ]);
    }

    public function test_validation_fails_for_invalid_profile_update()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson('/api/user/profile', [
            'email' => 'invalid-email'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_validation_fails_for_invalid_password_update()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->patchJson('/api/user/password', [
            'password' => 'short'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_update_password_validates_input()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->patchJson('/api/user/password', [
            'current_password' => 'wrong_password',
            'password' => 'new_password',
            'password_confirmation' => 'new_password'
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'رمز عبور فعلی اشتباه است'
            ]);
    }
} 