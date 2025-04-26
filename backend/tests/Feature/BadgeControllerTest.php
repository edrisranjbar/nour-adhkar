<?php

namespace Tests\Feature;

use App\Models\Badge;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BadgeControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $badges;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test user
        $this->user = User::factory()->create([
            'heart_score' => 0
        ]);
        
        // Create predefined badges
        $this->badges = [
            Badge::create([
                'name' => 'تازه‌کار',
                'description' => 'کاربر جدیدی که به تازگی به پلتفرم ملحق شده است',
                'icon' => 'fa-solid fa-seedling',
                'color' => '#4CAF50',
                'points_required' => 0,
            ]),
            Badge::create([
                'name' => 'مستقر',
                'description' => 'کاربری که مدتی در پلتفرم فعال بوده است',
                'icon' => 'fa-solid fa-house',
                'color' => '#2196F3',
                'points_required' => 100,
            ]),
            Badge::create([
                'name' => 'مستمر',
                'description' => 'کاربری که به صورت مداوم و منظم فعالیت می‌کند',
                'icon' => 'fa-solid fa-calendar-check',
                'color' => '#9C27B0',
                'points_required' => 500,
            ]),
        ];
    }

    public function test_can_get_all_badges()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/badges');

        $response->assertStatus(200)
            ->assertJsonCount(3)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'name',
                    'description',
                    'icon',
                    'color',
                    'points_required'
                ]
            ]);
    }

    public function test_can_get_user_badges()
    {
        // Award a badge to the user
        $this->user->badges()->attach($this->badges[0]->id, [
            'earned_at' => now(),
            'user_id' => $this->user->id
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/user/badges');

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'name',
                    'description',
                    'icon',
                    'color',
                    'points_required',
                    'earned_at'
                ]
            ]);
    }

    public function test_can_check_and_award_badges()
    {
        // Set user's heart score to 150
        $this->user->update(['heart_score' => 150]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/user/check-badges');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'badges' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'icon',
                        'color',
                        'points_required',
                        'earned_at'
                    ]
                ]
            ]);

        // Refresh user to get updated badges
        $this->user->refresh();
        
        // Check that user has the appropriate badges
        $this->assertTrue($this->user->badges()->where('badge_id', $this->badges[0]->id)->exists());
        $this->assertTrue($this->user->badges()->where('badge_id', $this->badges[1]->id)->exists());
        $this->assertFalse($this->user->badges()->where('badge_id', $this->badges[2]->id)->exists());
    }

    public function test_can_award_specific_badge()
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/user/badges/{$this->badges[0]->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'badge' => [
                    'id',
                    'name',
                    'description',
                    'icon',
                    'color',
                    'points_required'
                ]
            ]);

        // Refresh user to get updated badges
        $this->user->refresh();
        
        // Check that the badge is attached to the user
        $this->assertTrue($this->user->badges()->where('badge_id', $this->badges[0]->id)->exists());
    }

    public function test_cannot_award_duplicate_badge()
    {
        // Award the badge first
        $this->user->badges()->attach($this->badges[0]->id, [
            'earned_at' => now(),
            'user_id' => $this->user->id
        ]);

        // Try to award the same badge again
        $response = $this->actingAs($this->user)
            ->postJson("/api/user/badges/{$this->badges[0]->id}");

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'کاربر قبلاً این نشان را دریافت کرده است'
            ]);
    }

    public function test_can_remove_badge()
    {
        // Award the badge first
        $this->user->badges()->attach($this->badges[0]->id, [
            'earned_at' => now(),
            'user_id' => $this->user->id
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/user/badges/{$this->badges[0]->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'نشان با موفقیت حذف شد'
            ]);

        // Refresh user to get updated badges
        $this->user->refresh();
        
        // Check that the badge is no longer attached to the user
        $this->assertFalse($this->user->badges()->where('badge_id', $this->badges[0]->id)->exists());
    }

    public function test_cannot_remove_nonexistent_badge()
    {
        $response = $this->actingAs($this->user)
            ->deleteJson("/api/user/badges/{$this->badges[0]->id}");

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'کاربر این نشان را ندارد'
            ]);
    }

    public function test_unauthorized_access_to_badge_routes()
    {
        $routes = [
            ['GET', '/api/badges'],
            ['GET', '/api/user/badges'],
            ['POST', '/api/user/check-badges'],
            ['POST', '/api/user/badges/1'],
            ['DELETE', '/api/user/badges/1'],
        ];

        foreach ($routes as $route) {
            $response = $this->json($route[0], $route[1]);
            $response->assertStatus(401);
        }
    }
} 