<?php

namespace Tests\Feature\Api;

use App\Models\League;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeagueControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed minimal leagues
        League::factory()->create([
            'name' => 'لیگ مبتدی',
            'min_points' => 0,
            'max_points' => 99,
            'icon' => 'fa-star',
            'color' => '#4F46E5'
        ]);
        League::factory()->create([
            'name' => 'لیگ متوسط',
            'min_points' => 100,
            'max_points' => 199,
            'icon' => 'fa-star-half',
            'color' => '#22C55E'
        ]);
    }

    public function test_requires_authentication(): void
    {
        $response = $this->getJson('/api/user/league-progress');
        $response->assertStatus(401);
    }

    public function test_returns_progress_payload_for_authenticated_user(): void
    {
        $user = User::factory()->create(['score' => 50]);
        $response = $this->actingAs($user)
            ->getJson('/api/user/league-progress');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'current_score',
                'next_league_points',
                'next_league',
                'current_league'
            ]);

        $data = $response->json();
        $this->assertEquals(50, $data['current_score']);
        $this->assertEquals(100, $data['next_league_points']);
        $this->assertNotNull($data['current_league']);
    }
}


