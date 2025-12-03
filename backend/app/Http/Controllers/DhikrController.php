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

        // Validate request
        $request->validate([
            'count' => 'nullable|integer|min:1|max:10000'
        ]);

        // Update user statistics
        $user->total_dhikrs++;

        // Add 10 points to the user's score
        $user->score += 10;

        // Calculate heart score increase based on dhikr count
        $dhikrCount = $request->input('count', 33); // Default to 33 if not provided
        $heartScoreIncrease = max(1, (int) ceil($dhikrCount / 10)); // At least 1, round up division by 10

        // Add heart score points based on dhikr count
        $user->heart_score = min(($user->heart_score ?? 0) + $heartScoreIncrease, 100);

        // Track daily completion counts
        $today = now()->format('Y-m-d');
        $dailyCounts = $user->daily_counts ?? [];
        $todayCount = ($dailyCounts[$today] ?? 0) + 1;
        $dailyCounts[$today] = $todayCount;
        $user->daily_counts = $dailyCounts;

        // Track completion date (for streak calculation)
        $completedDates = $user->completed_dates ?? [];
        if (!in_array($today, $completedDates)) {
            $completedDates[] = $today;
            $user->completed_dates = $completedDates;
        }

        // Update last_dhikr_completed_at for consistency
        $user->last_dhikr_completed_at = now();

        // Check if daily collection is completed (award additional 10 heart score points)
        $collectionCompleted = $this->isDailyCollectionCompleted($todayCount);

        if ($collectionCompleted) {
            // Award additional 10 heart score points for completing the daily collection
            $user->heart_score = min(($user->heart_score ?? 0) + 10, 100);
        }

        $user->save();

        $this->badgeService->updateStreak($user);

        // Check and award badges
        $newBadgeAwarded = $this->badgeService->checkAndAwardBadges($user);

        return response()->json([
            'success' => true,
            'user' => $user->fresh(),
            'new_badge_awarded' => $newBadgeAwarded,
            'collection_completed' => $collectionCompleted,
            'today_count' => $todayCount,
            'heart_score_increase' => $heartScoreIncrease
        ]);
    }

    /**
     * Check if the user has completed the daily collection for today
     */
    private function isDailyCollectionCompleted($todayCount)
    {
        try {
            // Get daily collection count (assuming 4 dhikrs like in mobile app)
            // This should match the number of dhikrs loaded in the daily collection
            $dailyCollectionCount = 4;

            return $todayCount >= $dailyCollectionCount;
        } catch (\Exception $e) {
            // If there's any error, don't award collection completion
            return false;
        }
    }
} 