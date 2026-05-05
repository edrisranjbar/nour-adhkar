<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
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
        $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . Auth::id(),
            'password' => 'nullable|string|min:6'
        ]);

        try {
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
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed'
        ]);

        $user = Auth::user();
        
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'رمز عبور فعلی اشتباه است'
            ], 422);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'message' => 'رمز عبور با موفقیت به‌روزرسانی شد'
        ]);
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

            // Get today's dhikr count from daily_counts
            $dailyCounts = $user->daily_counts ?? [];
            $todayCount = $dailyCounts[$today] ?? 0;

            // Get favorite dhikr count (total days with completions)
            $favoriteCount = count($user->completed_dates ?? []);

            return response()->json([
                'today_count' => $todayCount,
                'favorite_count' => $favoriteCount,
                'streak' => $user->streak,
                'total_dhikrs' => $user->total_dhikrs ?? 0,
                'completed_dates' => $user->completed_dates ?? []
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

    /**
     * Get user dashboard data
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDashboard()
    {
        try {
            $user = Auth::user();
            
            // Get user stats
            $stats = [
                'totalDhikr' => $user->total_dhikrs ?? 0,
                'totalContributions' => $user->contributions()->count(),
                'totalDonations' => $user->donations()->count(),
                'streak' => $user->streak,
            ];

            // Get recent activities
            $recentActivities = collect();
            
            // Add recent dhikr completions
            $recentDhikrs = $user->completed_dates ?? [];
            foreach ($recentDhikrs as $date) {
                $recentActivities->push([
                    'id' => 'dhikr-' . $date,
                    'type' => 'dhikr',
                    'description' => 'تکمیل ذکر در تاریخ ' . $date,
                    'date' => $date
                ]);
            }

            // Add recent contributions
            $contributions = $user->contributions()
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($contribution) {
                    return [
                        'id' => 'contribution-' . $contribution->id,
                        'type' => 'contribution',
                        'description' => 'مشارکت جدید: ' . $contribution->title,
                        'date' => $contribution->created_at
                    ];
                });
            $recentActivities = $recentActivities->concat($contributions);

            // Add recent donations
            $donations = $user->donations()
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($donation) {
                    return [
                        'id' => 'donation-' . $donation->id,
                        'type' => 'donation',
                        'description' => 'حمایت مالی: ' . number_format($donation->amount) . ' تومان',
                        'date' => $donation->created_at
                    ];
                });
            $recentActivities = $recentActivities->concat($donations);

            // Sort activities by date and take the 10 most recent
            $recentActivities = $recentActivities
                ->sortByDesc('date')
                ->take(10)
                ->values();

            return response()->json([
                'success' => true,
                'user' => new UserResource($user),
                'stats' => $stats,
                'recentActivities' => $recentActivities
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در دریافت اطلاعات داشبورد',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getCompletedDays()
    {
        try {
            $user = Auth::user();
            return response()->json([
                'dates' => $user->completed_dates ?? []
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'خطا در دریافت تاریخ‌های تکمیل شده',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 