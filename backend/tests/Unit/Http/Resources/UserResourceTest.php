<?php

namespace Tests\Unit\Http\Resources;

use Tests\TestCase;
use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class UserResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_resource_transforms_user_model_correctly()
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'avatar' => 'avatar.jpg',
            'heart_score' => 100,
            'streak' => 5,
            'role' => 'user',
            'active' => true,
            'badges' => ['test_badge_date' => now()->toDateTimeString()],
            'last_login_at' => now()->subDay(),
        ]);

        $resource = new UserResource($user);
        $array = $resource->toArray(request());

        $this->assertEquals($user->id, $array['id']);
        $this->assertEquals($user->name, $array['name']);
        $this->assertEquals($user->email, $array['email']);
        $this->assertEquals($user->avatar, $array['avatar']);
        $this->assertEquals($user->heart_score, $array['heart_score']);
        $this->assertEquals($user->streak, $array['streak']);
        $this->assertEquals($user->has_new_badge, $array['has_new_badge']);
        $this->assertEquals($user->role, $array['role']);
        $this->assertEquals($user->active, $array['active']);
        $this->assertEquals($user->created_at, $array['created_at']);
        $this->assertEquals($user->updated_at, $array['updated_at']);
    }
} 