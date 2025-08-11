<?php

namespace Tests\Unit\Services;

use App\Models\League;
use App\Models\User;
use App\Services\LeagueService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeagueServiceTest extends TestCase
{
    use RefreshDatabase;

    private LeagueService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new LeagueService();

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
        League::factory()->create([
            'name' => 'لیگ حرفه‌ای',
            'min_points' => 200,
            'max_points' => 9999,
            'icon' => 'fa-crown',
            'color' => '#F59E0B'
        ]);
    }

    public function test_update_user_league_assigns_correct_league(): void
    {
        $user = User::factory()->create(['score' => 0]);

        $this->service->updateUserLeague($user);
        $user->refresh();

        $this->assertNotNull($user->league);
        $this->assertEquals('لیگ مبتدی', $user->league->name);

        $user->score = 120;
        $user->save();
        $this->service->updateUserLeague($user);
        $user->refresh();

        $this->assertEquals('لیگ متوسط', $user->league->name);
    }

    public function test_get_progress_to_next_league_returns_expected_structure(): void
    {
        $user = User::factory()->create(['score' => 50]);

        $progress = $this->service->getProgressToNextLeague($user);

        $this->assertArrayHasKey('current_score', $progress);
        $this->assertArrayHasKey('next_league_points', $progress);
        $this->assertArrayHasKey('next_league', $progress);
        $this->assertArrayHasKey('current_league', $progress);

        $this->assertEquals(50, $progress['current_score']);
        $this->assertNotNull($progress['next_league_points']);
        $this->assertNotNull($progress['current_league']);
        $this->assertEquals(100, $progress['next_league_points']);
    }

    public function test_get_progress_when_in_highest_league_has_null_next(): void
    {
        $user = User::factory()->create(['score' => 300]);

        $progress = $this->service->getProgressToNextLeague($user);

        $this->assertEquals(300, $progress['current_score']);
        $this->assertNull($progress['next_league']);
        $this->assertNotNull($progress['current_league']);
        $this->assertEquals(9999, $progress['current_league']->max_points);
    }
}


