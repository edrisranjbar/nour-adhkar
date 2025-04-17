<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\BadgeService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    protected $badgeService;

    public function __construct(BadgeService $badgeService)
    {
        $this->badgeService = $badgeService;
    }

    /**
     * Update user's name
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateName(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);

        $user = Auth::user();
        $user->name = $request->name;
        $user->save();

        return response()->json([
            'message' => 'Name updated successfully',
            'user' => new UserResource($user)
        ]);
    }

    /**
     * Update user's password
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePassword(Request $request)
    {
        $request->validate(['password' => 'required|string|min:6']);

        $user = Auth::user();
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'message' => 'Password updated successfully'
        ]);
    }

    /**
     * Update user's heart score
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateHeartScore(Request $request)
    {
        try {
            $request->validate(['heart_score' => 'required|integer|min:0']);

            $user = Auth::user();
            $new_score = min((int)$request->heart_score, 100);
            $user->heart_score = $new_score;
            $user->save();

            // Check and award badges
            $badgeAwarded = $this->badgeService->checkAndAwardBadges($user);

            // Get updated user data with badges
            $user->refresh();

            return response()->json([
                'message' => 'Heart score updated successfully',
                'user' => new UserResource($user),
                'badges' => $user->badges,
                'badge_awarded' => $badgeAwarded
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to update heart score',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user statistics
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserStats()
    {
        try {
            $user = Auth::user();
            $today = now()->format('Y-m-d');
            
            // Get today's dhikr count
            $todayCount = in_array($today, $user->completed_dates ?? []) ? 1 : 0;
            
            // Get favorite dhikr count
            $favoriteCount = count($user->completed_dates ?? []);
            
            return response()->json([
                'todayCount' => $todayCount,
                'favoriteCount' => $favoriteCount,
                'streak' => $user->streak,
                'heartScore' => $user->heart_score ?? 0,
                'totalDhikrs' => $user->total_dhikrs ?? 0
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve user stats',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user's avatar
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAvatar(Request $request)
    {
        try {
            if (!$request->hasFile('avatar')) {
                return response()->json([
                    'message' => 'No avatar file provided'
                ], 400);
            }

            $user = auth()->user();
            $file = $request->file('avatar');
            
            // Delete old avatar if exists and not default
            if ($user->avatar && $user->avatar != 'avatars/default.png') {
                Storage::disk('public')->delete($user->avatar);
            }

            // Store new avatar
            $path = $file->store('avatars', 'public');
            
            // Update user
            $user->avatar = $path;
            $user->save();

            return response()->json([
                'message' => 'Avatar updated successfully',
                'avatar_url' => url('storage/' . $path),
                'user' => new UserResource($user)
            ]);

        } catch (Exception $e) {
            Log::error('Avatar upload error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to upload avatar',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 