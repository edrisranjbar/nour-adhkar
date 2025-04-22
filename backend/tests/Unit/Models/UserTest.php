<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\User;
use App\Models\Post;
use App\Models\Donation;
use App\Models\UserDhikr;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_required_fields()
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password',
            'role' => 'user',
            'active' => true,
            'avatar' => 'avatar.jpg',
            'score' => 100,
            'completed_dates' => [
                now()->subDays(4)->format('Y-m-d'),
                now()->subDays(3)->format('Y-m-d'),
                now()->subDays(2)->format('Y-m-d'),
                now()->subDays(1)->format('Y-m-d'),
                now()->format('Y-m-d')
            ],
            'badges' => ['early_bird', 'consistent'],
            'last_login_at' => now(),
            'last_dhikr_completed_at' => now(),
            'favorites' => ['post_1', 'post_2']
        ]);

        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertEquals('user', $user->role);
        $this->assertTrue($user->active);
        $this->assertEquals('avatar.jpg', $user->avatar);
        $this->assertEquals(100, $user->score);
        $this->assertEquals(5, $user->streak);
        $this->assertEquals(['early_bird', 'consistent'], $user->badges);
        $this->assertNotNull($user->last_login_at);
        $this->assertNotNull($user->last_dhikr_completed_at);
        $this->assertEquals(['post_1', 'post_2'], $user->favorites);
    }

    public function test_user_can_have_posts()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(Post::class, $user->posts->first());
        $this->assertEquals($post->id, $user->posts->first()->id);
    }

    public function test_user_can_have_user_dhikrs()
    {
        $user = User::factory()->create();
        $userDhikr = UserDhikr::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(UserDhikr::class, $user->userDhikrs->first());
        $this->assertEquals($userDhikr->id, $user->userDhikrs->first()->id);
    }

    public function test_user_can_have_donations()
    {
        $user = User::factory()->create();
        $donation = Donation::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(Donation::class, $user->donations->first());
        $this->assertEquals($donation->id, $user->donations->first()->id);
    }

    public function test_user_has_default_values()
    {
        $user = User::factory()->create();

        $this->assertEquals('user', $user->role);
        $this->assertTrue($user->active);
        $this->assertEquals(0, $user->score);
        $this->assertEquals(0, $user->streak);
        $this->assertEquals([], $user->badges);
        $this->assertEquals([], $user->favorites);
        $this->assertEquals([], $user->completed_dates);
    }

    public function test_user_has_admin_scope()
    {
        User::factory()->create(['role' => 'admin']);
        User::factory()->count(3)->create(['role' => 'user']);

        $admins = User::admin()->get();

        $this->assertCount(1, $admins);
        $this->assertEquals('admin', $admins->first()->role);
    }

    public function test_user_has_active_scope()
    {
        User::factory()->create(['active' => true]);
        User::factory()->create(['active' => false]);

        $activeUsers = User::active()->get();

        $this->assertCount(1, $activeUsers);
        $this->assertTrue($activeUsers->first()->active);
    }

    public function test_user_has_favorite_scope()
    {
        $user = User::factory()->create(['favorites' => ['post_1', 'post_2']]);
        User::factory()->create(['favorites' => ['post_3']]);

        $favoriteUsers = User::favorite('post_1')->get();

        $this->assertCount(1, $favoriteUsers);
        $this->assertEquals($user->id, $favoriteUsers->first()->id);
    }

    public function test_attributes_are_cast_correctly()
    {
        $user = new User();
        
        $this->assertEquals([
            'id' => 'integer',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'active' => 'boolean',
            'score' => 'integer',
            'badges' => 'json',
            'favorites' => 'json',
            'completed_dates' => 'json',
            'heart_score' => 'integer',
            'last_login_at' => 'datetime',
            'last_dhikr_completed_at' => 'datetime',
            'total_dhikrs' => 'integer'
        ], $user->getCasts());
    }

    public function test_fillable_attributes()
    {
        $user = new User();
        
        $this->assertEquals([
            'name',
            'email',
            'password',
            'role',
            'active',
            'avatar',
            'score',
            'streak',
            'badges',
            'last_login_at',
            'last_dhikr_completed_at',
            'favorites',
            'total_dhikrs'
        ], $user->getFillable());
    }

    public function test_user_has_jwt_methods()
    {
        $user = User::factory()->create();
        
        $this->assertEquals($user->id, $user->getJWTIdentifier());
        $this->assertEquals([], $user->getJWTCustomClaims());
    }

    public function test_user_has_new_badge_attribute()
    {
        $user = User::factory()->create([
            'badges' => ['early_bird'],
            'last_login_at' => now()->subDay()
        ]);
        
        $this->assertFalse($user->has_new_badge);
        
        $user->badges = [
            'early_bird' => true,
            'new_badge_date' => now()->format('Y-m-d H:i:s')
        ];
        $user->save();
        
        $this->assertTrue($user->has_new_badge);
        
        // Test with old badge date
        $user->badges = [
            'early_bird' => true,
            'new_badge_date' => now()->subDays(2)->format('Y-m-d H:i:s')
        ];
        $user->save();
        
        $this->assertFalse($user->has_new_badge);
    }

    public function test_streak_calculation()
    {
        $user = User::factory()->create();
        
        // No completed dates
        $this->assertEquals(0, $user->streak);
        
        // One completed date
        $user->completed_dates = [now()->format('Y-m-d')];
        $user->save();
        $this->assertEquals(1, $user->streak);
        
        // Two consecutive days
        $user->completed_dates = [
            now()->subDay()->format('Y-m-d'),
            now()->format('Y-m-d')
        ];
        $user->save();
        $this->assertEquals(2, $user->streak);
        
        // Three consecutive days
        $user->completed_dates = [
            now()->subDays(2)->format('Y-m-d'),
            now()->subDay()->format('Y-m-d'),
            now()->format('Y-m-d')
        ];
        $user->save();
        $this->assertEquals(3, $user->streak);
        
        // Break in streak
        $user->completed_dates = [
            now()->subDays(3)->format('Y-m-d'),
            now()->format('Y-m-d')
        ];
        $user->save();
        $this->assertEquals(1, $user->streak);
    }
} 