<?php

namespace App\Services;

use App\Models\User;
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
        $badges = [];
        foreach ($this->badges as $key => $badge) {
            $badges[$key] = false;
            $badges["{$key}_date"] = null;
        }
        $user->badges = $badges;
        $user->save();
    }

    public function checkAndAwardBadges(User $user)
    {
        // Initialize badges if they don't exist
        if (!$user->badges) {
            $this->initializeBadges($user);
        }
        
        $badges = $user->badges;
        $updated = false;

        // Check beginner badge
        if (!isset($badges['beginner']) || !$badges['beginner']) {
            if ($user->total_dhikrs >= $this->badges['beginner']['condition']) {
                $badges['beginner'] = true;
                $badges['beginner_date'] = now()->toDateString();
                $updated = true;
            }
        }

        // Check hardworker badge
        if (!isset($badges['hardworker']) || !$badges['hardworker']) {
            if ($user->total_dhikrs >= $this->badges['hardworker']['condition']) {
                $badges['hardworker'] = true;
                $badges['hardworker_date'] = now()->toDateString();
                $updated = true;
            }
        }

        // Check consistent badge
        if (!isset($badges['consistent']) || !$badges['consistent']) {
            if ($user->streak >= $this->badges['consistent']['condition']) {
                $badges['consistent'] = true;
                $badges['consistent_date'] = now()->toDateString();
                $updated = true;
            }
        }

        // Check golden heart badge
        if (!isset($badges['golden_heart']) || !$badges['golden_heart']) {
            if ($user->heart_score >= $this->badges['golden_heart']['condition']) {
                $badges['golden_heart'] = true;
                $badges['golden_heart_date'] = now()->toDateString();
                $updated = true;
            }
        }

        if ($updated) {
            $user->badges = $badges;
            $user->save();
        }

        return $updated;
    }

    public function updateStreak(User $user)
    {
        $today = Carbon::today();
        $todayString = $today->toDateString();
        
        // Initialize completed_dates if null
        $completedDates = $user->completed_dates ?? [];
        
        // If already completed today, no need to update
        if (in_array($todayString, $completedDates)) {
            return;
        }
        
        // Add today to completed dates
        $completedDates[] = $todayString;
        $user->completed_dates = $completedDates;
        $user->last_dhikr_completed_at = $today;
        $user->save();
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