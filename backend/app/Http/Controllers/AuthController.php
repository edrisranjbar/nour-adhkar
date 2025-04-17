<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Laravel\Passport\Token;

class AuthController extends Controller
{
    public function get()
    {
        return new UserResource(auth()->user());
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $token = $user->createToken('Personal Access Token')->accessToken;

            return response()->json([
                'user' => new UserResource($user),
                'token' => $token,
                'message' => 'User registered successfully'
            ], 201);
        } catch (Exception $e) {
            return response()->json(['message' => 'Registration failed', 'error' => $e->getMessage()], 500);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('Personal Access Token')->accessToken;

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
            'message' => 'Login successful'
        ]);
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->token()->revoke();
            return response()->json([
                'message' => 'Successfully logged out'
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => 'Logout failed', 'error' => $e->getMessage()], 500);
        }
    }

    // Admin methods
    public function updateUserStatus(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            $user->update([
                'is_active' => $request->is_active,
            ]);

            return response()->json([
                'message' => 'User status updated successfully',
                'user' => new UserResource($user)
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to update user status', 'error' => $e->getMessage()], 500);
        }
    }

    public function updateUserRole(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            $user->update([
                'is_admin' => $request->is_admin,
            ]);

            return response()->json([
                'message' => 'User role updated successfully',
                'user' => new UserResource($user)
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to update user role', 'error' => $e->getMessage()], 500);
        }
    }

    public function deleteUser($id)
    {
        try {
            $user = User::findOrFail($id);

            // Revoke all tokens
            $user->tokens->each(function (Token $token) {
                $token->revoke();
            });

            // Delete user avatar if exists
            if ($user->avatar && $user->avatar != 'avatars/default.png') {
                Storage::disk('public')->delete($user->avatar);
            }

            $user->delete();

            return response()->json([
                'message' => 'User deleted successfully'
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to delete user', 'error' => $e->getMessage()], 500);
        }
    }

    public function users()
    {
        try {
            return UserResource::collection(User::latest()->paginate(10));
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to fetch users', 'error' => $e->getMessage()], 500);
        }
    }
}
