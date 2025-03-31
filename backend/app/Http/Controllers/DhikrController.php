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
        
        // Your existing dhikr creation logic here
        
        // Update user statistics
        $user->total_dhikrs++;
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