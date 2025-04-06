<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Storage;
use App\Services\BadgeService;

class AuthController extends Controller
{
    protected $badgeService;

    public function __construct(BadgeService $badgeService)
    {
        $this->badgeService = $badgeService;
    }

    public function get(Request $request) {
        return response()->json([
            'user' => [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'avatar' => $request->user()->avatar,
                'heart_score' => $request->user()->heart_score,
                'score' => $request->user()->score,
                'created_at' => $request->user()->created_at,
                'avatar' => $request->user()->avatar,
                'badges' => $request->user()->badges,
                'role' => $request->user()->role,
            ]
        ]);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = JWTAuth::fromUser($user);
        
        $this->badgeService->initializeBadges($user);

        return response()->json([
            'success' => true,
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'نام کاربری یا رمز عبور اشتباه است'], 401);
        }

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => Auth::user(),
        ]);
    }

    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'خروج از سیستم با خطا مواجه شد'], 500);
        }
    }

    public function changeName(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);

        $user = Auth::user();
        $user->name = $request->name;
        $user->save();

        return response()->json(['success' => true, 'user' => $user]);
    }

    public function changePassword(Request $request)
    {
        $request->validate(['password' => 'required|string|min:6']);

        $user = Auth::user();
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json(['success' => true]);
    }

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
                'success' => true,
                'user' => $user,
                'badges' => $user->badges,
                'badge_awarded' => $badgeAwarded
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در بروزرسانی امتیاز قلب',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateAvatar(Request $request)
    {
        try {
            if (!$request->hasFile('avatar')) {
                return response()->json([
                    'success' => false,
                    'message' => 'فایل تصویر پروفایل ارسال نشده است'
                ], 400);
            }

            $user = auth()->user();
            $file = $request->file('avatar');
            
            // Delete old avatar if exists and not default
            if ($user->avatar && !str_contains($user->avatar, 'default-avatar')) {
                $oldPath = str_replace(url('storage/'), '', $user->avatar);
                Storage::disk('public')->delete($oldPath);
            }

            // Store new avatar
            $path = $file->store('avatars', 'public');
            
            // Generate full URL for the avatar
            $avatarUrl = url('storage/' . $path);

            // Update user
            $user->avatar = $avatarUrl;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'تصویر پروفایل با موفقیت بروزرسانی شد',
                'avatar_url' => $avatarUrl
            ]);

        } catch (\Exception $e) {
            \Log::error('Avatar upload error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'خطا در آپلود تصویر پروفایل'
            ], 500);
        }
    }

    /**
     * Get a list of all users for admin
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function adminIndex(Request $request)
    {
        // Check if user is admin
        if (Auth::user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'شما دسترسی لازم برای این عملیات را ندارید'], 403);
        }

        $perPage = $request->query('per_page', 10);
        $search = $request->query('search', '');
        $role = $request->query('role', '');
        $sortBy = $request->query('sort', 'created_at_desc');

        $query = User::query();

        // Apply search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Apply role filter
        if ($role) {
            $query->where('role', $role);
        }

        // Apply sorting
        switch ($sortBy) {
            case 'created_at_asc':
                $query->orderBy('created_at', 'asc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $users = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'users' => $users
        ]);
    }

    /**
     * Get a specific user by ID for admin
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function adminShow($id)
    {
        // Check if user is admin
        if (Auth::user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'شما دسترسی لازم برای این عملیات را ندارید'], 403);
        }

        $user = User::findOrFail($id);

        return response()->json([
            'success' => true,
            'user' => $user
        ]);
    }

    /**
     * Create a new user by admin
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function adminStore(Request $request)
    {
        // Check if user is admin
        if (Auth::user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'شما دسترسی لازم برای این عملیات را ندارید'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:user,admin',
            'active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'active' => $request->active ?? true,
        ]);

        // Initialize badges for new user
        $this->badgeService->initializeBadges($user);

        return response()->json([
            'success' => true,
            'user' => $user,
        ], 201);
    }

    /**
     * Update a user by admin
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function adminUpdate(Request $request, $id)
    {
        // Check if user is admin
        if (Auth::user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'شما دسترسی لازم برای این عملیات را ندارید'], 403);
        }

        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'email' => 'string|email|max:255|unique:users,email,'.$id,
            'role' => 'in:user,admin',
            'active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // Update user data
        if ($request->has('name')) $user->name = $request->name;
        if ($request->has('email')) $user->email = $request->email;
        if ($request->has('role')) $user->role = $request->role;
        if ($request->has('active')) $user->active = $request->active;

        $user->save();

        return response()->json([
            'success' => true,
            'user' => $user,
        ]);
    }

    /**
     * Toggle user active status
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleStatus($id)
    {
        // Check if user is admin
        if (Auth::user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'شما دسترسی لازم برای این عملیات را ندارید'], 403);
        }

        $user = User::findOrFail($id);
        
        // Don't allow deactivating yourself
        if ($user->id === Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'شما نمی‌توانید وضعیت خود را تغییر دهید'
            ], 403);
        }

        $user->active = !$user->active;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => $user->active ? 'کاربر با موفقیت فعال شد' : 'کاربر با موفقیت غیرفعال شد',
            'user' => $user
        ]);
    }

}
