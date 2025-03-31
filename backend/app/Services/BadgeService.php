<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;

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
        $badges = $user->badges ?? [];
        $updated = false;

        // Check beginner badge
        if (!$badges['beginner'] && $user->total_dhikrs >= $this->badges['beginner']['condition']) {
            $badges['beginner'] = true;
            $badges['beginner_date'] = now()->toDateString();
            $updated = true;
        }

        // Check hardworker badge
        if (!$badges['hardworker'] && $user->total_dhikrs >= $this->badges['hardworker']['condition']) {
            $badges['hardworker'] = true;
            $badges['hardworker_date'] = now()->toDateString();
            $updated = true;
        }

        // Check consistent badge
        if (!$badges['consistent'] && $user->streak >= $this->badges['consistent']['condition']) {
            $badges['consistent'] = true;
            $badges['consistent_date'] = now()->toDateString();
            $updated = true;
        }

        // Check golden heart badge
        if (!$badges['golden_heart'] && $user->heart_score >= $this->badges['golden_heart']['condition']) {
            $badges['golden_heart'] = true;
            $badges['golden_heart_date'] = now()->toDateString();
            $updated = true;
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
        $lastDhikrDate = $user->last_dhikr_date ? Carbon::parse($user->last_dhikr_date) : null;

        if (!$lastDhikrDate) {
            $user->streak = 1;
        } else {
            $daysDifference = $today->diffInDays($lastDhikrDate);

            if ($daysDifference === 0) {
                // Already recorded today
                return;
            } elseif ($daysDifference === 1) {
                // Consecutive day
                $user->streak++;
            } else {
                // Streak broken
                $user->streak = 1;
            }
        }

        $user->last_dhikr_date = $today;
        $user->save();
    }

    public function awardBadge(User $user, string $badgeName)
    {
        if (!$user->badges) {
            $user->badges = new \stdClass();
        }

        // Only award if not already earned
        if (empty($user->badges->{$badgeName})) {
            $user->badges->{$badgeName} = true;
            $user->badges->{$badgeName . '_date'} = now();
            $user->save();
        }
    }
} 