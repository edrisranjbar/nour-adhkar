<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{

    public function get(Request $request) {
        response()->json([
            'user' => [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'avatar' => $request->user()->avatar,
                'heart_score' => $request->user()->heart_score,
                'score' => $request->user()->score,
                'created_at' => $request->user()->created_at,
                'avatar' => $request->user()->avatar
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

    public function logout(Request $request)
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

        $request->validate(['heart_score' => 'required|integer|min:0']);

        $user = Auth::user();
        $new_score = min((int)$request->heart_score, 100);
        $user->heart_score = $new_score;
        $user->save();

        return response()->json(['success' => true]);
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120'
        ]);

        try {
            $user = auth()->user();
            
            // Delete old avatar if exists
            if ($user->avatar && !str_contains($user->avatar, 'placeholder')) {
                $oldPath = str_replace('/storage/', '', $user->avatar);
                Storage::disk('public')->delete($oldPath);
            }

            // Store new avatar
            $path = $request->file('avatar')->store('avatars', 'public');
            
            // Update user record with full URL
            $user->avatar = url('storage/' . $path); // This ensures we get the full URL
            $user->save();

            return response()->json([
                'success' => true,
                'avatar_url' => $user->avatar
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error uploading avatar',
                'detail' => $e->getMessage()
            ], 500);
        }
    }

}
