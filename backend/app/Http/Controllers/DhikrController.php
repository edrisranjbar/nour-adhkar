<?php

namespace App\Http\Controllers;

use App\Models\UserDhikr;
use Illuminate\Http\Request;

class DhikrController extends Controller
{
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

        $request->validate([
            'count' => 'nullable|integer|min:1|max:10000'
        ]);

        $user->total_dhikrs++;

        $today = now()->format('Y-m-d');
        $dailyCounts = $user->daily_counts ?? [];
        $todayCount = ($dailyCounts[$today] ?? 0) + 1;
        $dailyCounts[$today] = $todayCount;
        $user->daily_counts = $dailyCounts;

        $completedDates = $user->completed_dates ?? [];
        if (!in_array($today, $completedDates)) {
            $completedDates[] = $today;
            $user->completed_dates = $completedDates;
        }

        $user->last_dhikr_completed_at = now();

        $collectionCompleted = $this->isDailyCollectionCompleted($todayCount) &&
                              !$this->isDailyCollectionCompleted($todayCount - 1);

        \Log::info("Dhikr completion - User: {$user->id}, TodayCount: $todayCount, CollectionCompleted: $collectionCompleted");

        $user->save();

        return response()->json([
            'success' => true,
            'user' => $user->fresh(),
            'collection_completed' => $collectionCompleted,
            'today_count' => $todayCount,
        ]);
    }

    private function isDailyCollectionCompleted($todayCount)
    {
        try {
            $dailyCollectionCount = 4;

            return $todayCount >= $dailyCollectionCount;
        } catch (\Exception $e) {
            return false;
        }
    }
}
