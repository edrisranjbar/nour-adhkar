<?php

namespace App\Services;

use App\Models\User;
use App\Models\League;
use Illuminate\Support\Facades\Log;

class LeagueService
{

    public function updateUserLeague(User $user): void
    {
        try {
            $userScore = $user->score;
            
            // Find the appropriate league based on score
            $currentLeague = League::where('min_points', '<=', $userScore)
                ->where('max_points', '>=', $userScore)
                ->first();

            // If no league found, assign to beginner league
            if (!$currentLeague) {
                $currentLeague = League::where('name', 'لیگ مبتدی')->first();
            }

            if ($currentLeague && $currentLeague->id !== $user->league_id) {
                $user->league_id = $currentLeague->id;
                $user->save();
            }
        } catch (\Exception $e) {
            Log::error('Error in LeagueService@updateUserLeague: ' . $e->getMessage());
        }
    }

    public function getProgressToNextLeague(User $user): array
    {
        try {
            $userScore = $user->score;
            
            // Ensure user has a league
            $this->updateUserLeague($user);
            $currentLeague = $user->league;
            
            if (!$currentLeague) {
                return [
                    'current_score' => $userScore,
                    'next_league_points' => null,
                    'next_league' => null,
                    'current_league' => null
                ];
            }

            // Find the next league based on min_points
            $nextLeague = League::where('min_points', '>', $currentLeague->min_points)
                ->orderBy('min_points')
                ->first();

            // If user is in the highest league
            if (!$nextLeague) {
                return [
                    'current_score' => $userScore,
                    'next_league_points' => $currentLeague->max_points,
                    'next_league' => null,
                    'current_league' => $currentLeague
                ];
            }

            return [
                'current_score' => $userScore,
                'next_league_points' => $nextLeague->min_points,
                'next_league' => $nextLeague,
                'current_league' => $currentLeague
            ];
        } catch (\Exception $e) {
            Log::error('Error in LeagueService@getProgressToNextLeague: ' . $e->getMessage());
            throw $e;
        }
    }
} 