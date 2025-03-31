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
            return response()->json(['error' => 'Invalid credentials'], 401);
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
            return response()->json(['success' => false, 'error' => 'Logout failed'], 500);
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
                'message' => 'Error updating heart score',
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
                    'message' => 'No avatar file provided'
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
                'message' => 'Avatar updated successfully',
                'avatar_url' => $avatarUrl
            ]);

        } catch (\Exception $e) {
            \Log::error('Avatar upload error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error uploading avatar'
            ], 500);
        }
    }

}
