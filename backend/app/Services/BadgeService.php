<?php

namespace App\Services;

use App\Models\User;
use App\Models\Badge;
use Carbon\Carbon;
use Illuminate\Log\Logger;

class BadgeService
{
    protected $badges = [
        'beginner' => [
            'title' => 'تازه‌کار',
            'condition' => 1, // total_dhikrs
        ],
        'hardworker' => [
            'title' => 'پرتلاش',
            'condition' => 100, // total_dhikrs
        ],
        'consistent' => [
            'title' => 'مداوم',
            'condition' => 7, // streak
        ],
        'golden_heart' => [
            'title' => 'قلب طلایی',
            'condition' => 100, // heart_score
        ],
    ];

    public function initializeBadges(User $user)
    {
        // Award beginner badge immediately upon user creation
        $beginnerBadge = Badge::where('name', 'تازه‌کار')->first();
        if ($beginnerBadge) {
            $user->badges()->attach($beginnerBadge->id, ['earned_at' => now()]);
        }
    }

    public function checkAndAwardBadges(User $user)
    {
        // Check for consistent badge (7-day streak)
        $consistentBadge = Badge::where('name', 'مداوم')->first();
        if ($consistentBadge && !$user->badges()->where('badge_id', $consistentBadge->id)->exists()) {
            if ($user->streak >= 7) {
                $user->badges()->attach($consistentBadge->id, ['earned_at' => now()]);
                return true;
            }
        }
        return false;
    }

    public function updateStreak(User $user)
    {
        $lastActivity = $user->last_activity_date;
        $today = Carbon::today();

        if (!$lastActivity) {
            $user->streak = 1;
            $user->last_activity_date = $today;
            $user->save();
            return;
        }

        $lastActivityDate = Carbon::parse($lastActivity)->startOfDay();
        $daysDifference = $today->diffInDays($lastActivityDate);

        if ($daysDifference === 0) {
            // Already recorded today
            return;
        } elseif ($daysDifference === 1) {
            // Consecutive day
            $user->streak += 1;
        } else {
            // Streak broken
            $user->streak = 1;
        }

        $user->last_activity_date = $today;
        $user->save();

        // Check for badges after updating streak
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