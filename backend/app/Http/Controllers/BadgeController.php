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
        $badges = Badge::all();
        return BadgeResource::collection($badges);
    }

    public function userBadges(User $user)
    {
        $badges = $user->badges()->withPivot('earned_at')->get();
        return BadgeResource::collection($badges);
    }

    public function checkAndAwardBadges(User $user)
    {
        $totalPoints = $user->heart_score;
        $badges = Badge::where('points_required', '<=', $totalPoints)->get();

        foreach ($badges as $badge) {
            if (!$user->badges()->where('badge_id', $badge->id)->exists()) {
                $user->badges()->attach($badge->id, ['earned_at' => now()]);
            }
        }

        return response()->json([
            'message' => 'نشان‌ها بررسی و اعطا شدند',
            'badges' => BadgeResource::collection($user->badges)
        ]);
    }

    public function awardBadge(User $user, Badge $badge)
    {
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