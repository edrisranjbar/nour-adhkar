<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
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
        $request->validate(['score' => 'required|integer|between:0,100']);

        $user = Auth::user();
        $user->heart_score = $request->score;
        $user->save();

        return response()->json(['success' => true]);
    }

}
