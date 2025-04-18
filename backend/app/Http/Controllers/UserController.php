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
     * Get user profile
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProfile()
    {
        try {
            $user = Auth::user();
            return response()->json([
                'success' => true,
                'profile' => new UserResource($user)
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در دریافت پروفایل',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user profile
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request)
    {
        try {
            $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email,' . Auth::id(),
                'password' => 'sometimes|string|min:6'
            ]);

            $user = Auth::user();
            
            if ($request->has('name')) {
                $user->name = $request->name;
            }
            
            if ($request->has('email')) {
                $user->email = $request->email;
            }
            
            if ($request->has('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'پروفایل با موفقیت به‌روزرسانی شد',
                'profile' => new UserResource($user)
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در به‌روزرسانی پروفایل',
                'error' => $e->getMessage()
            ], 500);
        }
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
            'message' => 'نام با موفقیت به‌روزرسانی شد',
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
            'message' => 'رمز عبور با موفقیت به‌روزرسانی شد'
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
                'message' => 'امتیاز قلب با موفقیت به‌روزرسانی شد',
                'user' => new UserResource($user),
                'badges' => $user->badges,
                'badge_awarded' => $badgeAwarded
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'خطا در به‌روزرسانی امتیاز قلب',
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
                'message' => 'خطا در دریافت آمار کاربر',
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
                    'message' => 'فایل تصویر پروفایل ارسال نشده است'
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
                'message' => 'تصویر پروفایل با موفقیت به‌روزرسانی شد',
                'avatar_url' => url('storage/' . $path),
                'user' => new UserResource($user)
            ]);

        } catch (Exception $e) {
            Log::error('Avatar upload error: ' . $e->getMessage());
            return response()->json([
                'message' => 'خطا در آپلود تصویر پروفایل',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 