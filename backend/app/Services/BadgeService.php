<?php

namespace App\Services;

use App\Models\User;
use App\Models\Badge;
use Carbon\Carbon;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Schema;

class BadgeService
{
    protected $badges = [
        'beginner' => [
            'title' => 'beginner',
            'condition' => 1, // total_dhikrs
        ],
        'hardworker' => [
            'title' => 'hardworker',
            'condition' => 100, // total_dhikrs
        ],
        'consistent' => [
            'title' => 'consistent',
            'condition' => 7, // streak
        ],
        'golden_heart' => [
            'title' => 'golden_heart',
            'condition' => 100, // heart_score
        ],
    ];

    public function initializeBadges(User $user)
    {
        $badges = [
            'beginner' => false,
            'beginner_date' => null,
            'hardworker' => false,
            'hardworker_date' => null,
            'consistent' => false,
            'consistent_date' => null,
            'golden_heart' => false,
            'golden_heart_date' => null,
        ];
        $user->badges = $badges;
        $user->save();
    }

    public function checkAndAwardBadges(User $user)
    {
        $awarded = false;
        $badges = $user->badges ?? [];

        // Ensure badge structure keys exist so tests can assert false values
        foreach (['beginner', 'hardworker', 'consistent', 'golden_heart'] as $key) {
            if (!array_key_exists($key, $badges)) {
                $badges[$key] = false;
            }
            $dateKey = $key . '_date';
            if (!array_key_exists($dateKey, $badges)) {
                $badges[$dateKey] = null;
            }
        }

        // Beginner: first dhikr
        if (($user->total_dhikrs ?? 0) >= 1 && empty($badges['beginner'])) {
            $badges['beginner'] = true;
            $badges['beginner_date'] = now()->toDateString();
            $awarded = true;
        }

        // Hardworker: 100 dhikrs
        if (($user->total_dhikrs ?? 0) >= 100 && empty($badges['hardworker'])) {
            $badges['hardworker'] = true;
            $badges['hardworker_date'] = now()->toDateString();
            $awarded = true;
        }

        // Consistent: 7-day streak
        if (($user->streak ?? 0) >= 7 && empty($badges['consistent'])) {
            $badges['consistent'] = true;
            $badges['consistent_date'] = now()->toDateString();
            $awarded = true;
        }

        // Golden heart: heart_score 100
        if (($user->heart_score ?? 0) >= 100 && empty($badges['golden_heart'])) {
            $badges['golden_heart'] = true;
            $badges['golden_heart_date'] = now()->toDateString();
            $awarded = true;
        }

        // Persist even if nothing awarded so keys are present
        $user->badges = $badges;
        $user->save();

        return $awarded;
    }

    public function updateStreak(User $user)
    {
        $today = Carbon::today()->toDateString();
        $dates = $user->completed_dates ?? [];

        if (empty($dates)) {
            $dates = [$today];
            $user->streak = 1;
        } else {
            $last = end($dates);
            if ($last === $today) {
                // already counted today
            } elseif ($last === Carbon::yesterday()->toDateString()) {
                $dates[] = $today;
                $user->streak = ($user->streak ?? 0) + 1;
            } else {
                $dates[] = $today;
                $user->streak = 1;
            }
        }

        $user->completed_dates = $dates;
        $user->last_dhikr_completed_at = Carbon::today();
        $user->save();

        $this->checkAndAwardBadges($user);
    }

    public function awardBadge(User $user, string $badgeName)
    {
        $badges = $user->badges ?? [];
        
        // Only award if not already earned
        if (empty($badges[$badgeName])) {
            $badges[$badgeName] = true;
            $badges[$badgeName . '_date'] = now()->toDateString();
            $user->badges = $badges;
            $user->save();
        }
    }
} 