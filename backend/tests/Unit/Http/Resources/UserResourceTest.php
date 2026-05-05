<?php

namespace Tests\Unit\Http\Resources;

use Tests\TestCase;
use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_resource_transforms_user_model_correctly()
    {
        $dates = [];
        for ($i = 4; $i >= 0; $i--) {
            $dates[] = now()->subDays($i)->format('Y-m-d');
        }
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'avatar' => 'avatar.jpg',
            'total_dhikrs' => 42,
            'completed_dates' => $dates,
            'role' => 'user',
            'active' => true,
            'last_login_at' => now()->subDay(),
        ]);

        $resource = new UserResource($user);
        $array = $resource->toArray(request());

        $this->assertEquals($user->id, $array['id']);
        $this->assertEquals($user->name, $array['name']);
        $this->assertEquals($user->email, $array['email']);
        $this->assertEquals($user->avatar, $array['avatar']);
        $this->assertEquals(42, $array['total_adhkar_completed']);
        $this->assertEquals(5, $array['streak']);
        $this->assertEquals($user->role, $array['role']);
        $this->assertEquals($user->active, $array['active']);
        $this->assertEquals($user->created_at, $array['created_at']);
        $this->assertEquals($user->updated_at, $array['updated_at']);
    }
}
