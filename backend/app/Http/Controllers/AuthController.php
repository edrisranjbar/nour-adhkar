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
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function get()
    {
        return response()->json([
            'data' => new UserResource(auth()->user())
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
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $token = auth()->login($user);

            return response()->json([
                'user' => new UserResource($user),
                'token' => $token,
                'message' => 'ثبت نام با موفقیت انجام شد'
            ], 201);
        } catch (Exception $e) {
            return response()->json(['message' => 'ثبت نام با خطا مواجه شد', 'error' => $e->getMessage()], 500);
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

        if (!$token = auth()->attempt(['email' => $request->email, 'password' => $request->password])) {
            return response()->json(['message' => 'نام کاربری یا رمز عبور اشتباه است'], 401);
        }

        $user = auth()->user();

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
            'message' => 'ورود موفقیت‌آمیز'
        ]);
    }

    public function logout(Request $request)
    {
        try {
            auth()->logout();
            return response()->json([
                'message' => 'خروج با موفقیت انجام شد'
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => 'خطا در خروج از حساب کاربری', 'error' => $e->getMessage()], 500);
        }
    }

    // Add a refresh token method for JWT
    public function refresh()
    {
        try {
            $token = auth()->refresh();
            
            return response()->json([
                'token' => $token,
                'expires_in' => auth()->factory()->getTTL() * 60,
                'message' => 'توکن با موفقیت به‌روزرسانی شد'
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => 'خطا در به‌روزرسانی توکن', 'error' => $e->getMessage()], 401);
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
                'message' => 'وضعیت کاربر با موفقیت به‌روزرسانی شد',
                'user' => new UserResource($user)
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => 'خطا در به‌روزرسانی وضعیت کاربر', 'error' => $e->getMessage()], 500);
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
                'message' => 'نقش کاربر با موفقیت به‌روزرسانی شد',
                'user' => new UserResource($user)
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => 'خطا در به‌روزرسانی نقش کاربر', 'error' => $e->getMessage()], 500);
        }
    }

    public function deleteUser($id)
    {
        try {
            $user = User::findOrFail($id);

            // Delete user avatar if exists
            if ($user->avatar && $user->avatar != 'avatars/default.png') {
                Storage::disk('public')->delete($user->avatar);
            }

            $user->delete();

            return response()->json([
                'message' => 'کاربر با موفقیت حذف شد'
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => 'خطا در حذف کاربر', 'error' => $e->getMessage()], 500);
        }
    }

    public function users()
    {
        try {
            return UserResource::collection(User::latest()->paginate(10));
        } catch (Exception $e) {
            return response()->json(['message' => 'خطا در دریافت لیست کاربران', 'error' => $e->getMessage()], 500);
        }
    }

    public function adminIndex(Request $request)
    {
        try {
            $query = User::query();

            // Search by name or email
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Filter by role
            if ($request->has('role') && $request->role) {
                $query->where('role', $request->role);
            }

            // Sort
            if ($request->has('sort')) {
                $sort = $request->sort;
                if ($sort === 'created_at_desc') {
                    $query->orderBy('created_at', 'desc');
                } elseif ($sort === 'created_at_asc') {
                    $query->orderBy('created_at', 'asc');
                } elseif ($sort === 'name_asc') {
                    $query->orderBy('name', 'asc');
                } elseif ($sort === 'name_desc') {
                    $query->orderBy('name', 'desc');
                }
            } else {
                $query->orderBy('created_at', 'desc');
            }

            $users = $query->paginate($request->input('per_page', 10));

            return response()->json([
                'success' => true,
                'users' => $users
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در دریافت لیست کاربران',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);
        $user->active = !$user->active;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'وضعیت کاربر با موفقیت به روزرسانی شد'
        ]);
    }

    // Password reset: request reset link
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // Always respond with success to avoid user enumeration
        $status = Password::sendResetLink(
            $request->only('email')
        );

        return response()->json([
            'success' => in_array($status, [Password::RESET_LINK_SENT, Password::INVALID_USER]) ? true : true,
            'message' => __($status)
        ]);
    }

    // Password reset: perform reset with token
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            return response()->json([
                'success' => true,
                'message' => __($status)
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __($status)
        ], 422);
    }

}
