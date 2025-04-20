<?php

namespace Tests\Unit\Console\Commands;

use Tests\TestCase;
use App\Models\User;
use App\Console\Commands\ResetHeartScores;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

class ResetHeartScoresTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_heart_scores_command_resets_all_scores()
    {
        // Create users with different heart scores
        User::factory()->create(['heart_score' => 100]);
        User::factory()->create(['heart_score' => 50]);
        User::factory()->create(['heart_score' => 0]); // This one shouldn't be affected

        // Run the command
        $this->artisan('users:reset-heart-scores')
            ->expectsOutput('Resetting all users heart scores to zero...')
            ->expectsOutput('2 users heart scores have been reset.')
            ->assertSuccessful();

        // Verify all heart scores are reset to 0
        $this->assertEquals(3, User::count()); // Total number of users
        $this->assertEquals(3, User::where('heart_score', 0)->count()); // All users should have 0 score
        $this->assertEquals(0, User::where('heart_score', '>', 0)->count()); // No users should have score > 0
    }

    public function test_reset_heart_scores_command_handles_empty_database()
    {
        $this->artisan('users:reset-heart-scores')
            ->expectsOutput('Resetting all users heart scores to zero...')
            ->expectsOutput('0 users heart scores have been reset.')
            ->assertSuccessful();

        $this->assertEquals(0, User::where('heart_score', '>', 0)->count());
    }
} 