<?php

namespace App\Http\Controllers;

use App\Models\UserDhikr;
use App\Services\BadgeService;
use Illuminate\Http\Request;

class DhikrController extends Controller
{
    protected $badgeService;

    public function __construct(BadgeService $badgeService)
    {
        $this->badgeService = $badgeService;
    }

    public function index(Request $request)
    {
        $query = UserDhikr::query();

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('title', 'like', "%{$search}%")
                  ->orWhere('arabic_text', 'like', "%{$search}%")
                  ->orWhere('translation', 'like', "%{$search}%");
        }

        $dhikrs = $query->with('user:id,name')
                       ->latest()
                       ->get();

        return response()->json([
            'data' => $dhikrs
        ]);
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