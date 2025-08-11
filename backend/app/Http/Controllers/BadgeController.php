<?php

namespace App\Http\Controllers;

use App\Http\Resources\BadgeResource;
use App\Models\Badge;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BadgeController extends Controller
{
    public function index()
    {
        // Return badges sorted by points_required ascending
        $badges = Badge::orderBy('points_required')->get();
        return BadgeResource::collection($badges);
    }

    public function userBadges()
    {
        $user = Auth::user();
        $badges = $user->badges()->withPivot('earned_at')->get();
        return BadgeResource::collection($badges);
    }

    public function checkAndAwardBadges(Request $request)
    {
        $user = Auth::user();
        // Ensure we have the latest values (e.g., updated heart_score)
        $user->refresh();
        $totalPoints = $user->heart_score ?? 0;
        // Award badges for both 0-point and threshold-based requirements
        $badges = Badge::where('points_required', '<=', $totalPoints)->get();

        // Attach all eligible badges without removing existing ones
        $now = now();
        $attachData = [];
        foreach ($badges as $badge) {
            // Only attach if not already present
            if (!$user->badges()->where('badge_id', $badge->id)->exists()) {
                $attachData[$badge->id] = ['earned_at' => $now];
            }
        }
        if (!empty($attachData)) {
            $user->badges()->syncWithoutDetaching($attachData);
        }

        return response()->json([
            'message' => 'نشان‌ها بررسی و اعطا شدند',
            'badges' => BadgeResource::collection($user->badges()->withPivot('earned_at')->get())
        ]);
    }

    public function awardBadge(Request $request, Badge $badge)
    {
        $user = Auth::user();
        if ($user->badges()->where('badge_id', $badge->id)->exists()) {
            return response()->json([
                'message' => 'کاربر قبلاً این نشان را دریافت کرده است'
            ], 400);
        }

        $user->badges()->attach($badge->id, ['earned_at' => now()]);

        return response()->json([
            'message' => 'نشان با موفقیت اعطا شد',
            'badge' => new BadgeResource($badge)
        ]);
    }

    public function removeBadge(Badge $badge)
    {
        $user = Auth::user();

        if (!$user->badges()->where('badge_id', $badge->id)->exists()) {
            return response()->json([
                'message' => 'کاربر این نشان را ندارد'
            ], 400);
        }

        $user->badges()->detach($badge->id);

        return response()->json([
            'message' => 'نشان با موفقیت حذف شد'
        ]);
    }
} 