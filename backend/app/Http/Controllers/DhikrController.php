<?php

namespace App\Http\Controllers;

use App\Services\BadgeService;
use Illuminate\Http\Request;

class DhikrController extends Controller
{
    protected $badgeService;

    public function __construct(BadgeService $badgeService)
    {
        $this->badgeService = $badgeService;
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        
        // Update user statistics
        $user->total_dhikrs++;
        
        // Track completion date
        $today = now()->format('Y-m-d');
        $completedDates = $user->completed_dates ?? [];
        if (!in_array($today, $completedDates)) {
            $completedDates[] = $today;
            $user->completed_dates = $completedDates;
        }
        
        $user->save();
        
        $this->badgeService->updateStreak($user);
        
        // Check and award badges
        $newBadgeAwarded = $this->badgeService->checkAndAwardBadges($user);
        
        return response()->json([
            'success' => true,
            'user' => $user->fresh(),
            'new_badge_awarded' => $newBadgeAwarded
        ]);
    }
} 