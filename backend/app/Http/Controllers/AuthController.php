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
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Notifications\WelcomeEmail;
use App\Notifications\ResetPasswordFa;

class AuthController extends Controller
{
    public function __construct()
    {
        // Set locale to Persian for all responses
        App::setLocale('fa');
    }
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
            $errors = $validator->errors();
            $firstError = $errors->first();
            return response()->json([
                'success' => false,
                'message' => $firstError,
                'errors' => $errors
            ], 422);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $token = auth()->login($user);

            // Send welcome email
            try {
                $user->notify(new WelcomeEmail());
            } catch (Exception $e) {
                // Log error but don't fail registration
                \Log::error('Failed to send welcome email: ' . $e->getMessage());
            }

            return response()->json([
                'user' => new UserResource($user),
                'token' => $token,
                'message' => 'ثبت نام با موفقیت انجام شد'
            ], 201);
        } catch (Exception $e) {
            \Log::error('Registration error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'خطا در ثبت نام. لطفاً دوباره تلاش کنید.'
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $firstError = $errors->first();
            return response()->json([
                'success' => false,
                'message' => $firstError,
                'errors' => $errors
            ], 422);
        }

        try {
            // Check if user exists
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'کاربری با این ایمیل در سیستم ثبت نشده است'
                ], 401);
            }

            // Check if account is active
            if (!$user->active) {
                return response()->json([
                    'success' => false,
                    'message' => 'حساب کاربری شما غیرفعال شده است. لطفاً با پشتیبانی تماس بگیرید.'
                ], 403);
            }

            // Verify password
            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'رمز عبور اشتباه است'
                ], 401);
            }

            // Generate token
            $token = auth()->login($user);

            // Update last login time
            $user->update(['last_login_at' => now()]);

            return response()->json([
                'success' => true,
                'user' => new UserResource($user),
                'token' => $token,
                'message' => 'ورود موفقیت‌آمیز'
            ]);
        } catch (Exception $e) {
            \Log::error('Login error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'خطا در ورود به سیستم. لطفاً دوباره تلاش کنید.'
            ], 500);
        }
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

            // Prevent admin from deleting their own account
            if (auth()->id() === $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'نمی‌توانید حساب کاربری خود را حذف کنید'
                ], 422);
            }

            // Delete user avatar if exists
            if ($user->avatar && $user->avatar != 'avatars/default.png') {
                Storage::disk('public')->delete($user->avatar);
            }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'کاربر با موفقیت حذف شد'
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'خطا در حذف کاربر', 'error' => $e->getMessage()], 500);
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
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $firstError = $errors->first();
            return response()->json([
                'success' => false,
                'message' => $firstError,
                'errors' => $errors
            ], 422);
        }

        // Check if user exists
        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'کاربری با این ایمیل در سیستم ثبت نشده است.'
            ], 404);
        }

        // Generate password reset token
        // Laravel's Password broker creates and stores the token, then we get it
        $token = Password::broker()->createToken($user);
        
        // Get the plain token from database (we need to store it temporarily)
        // Actually, Laravel's createToken stores hashed token, we need plain one for URL
        // So we'll generate our own token and store it properly
        $plainToken = Str::random(64);
        
        // Store hashed token in password_resets table (Laravel's way)
        DB::table('password_resets')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($plainToken),
                'created_at' => now()
            ]
        );

        // Send reset password email with plain token
        try {
            $user->notify(new ResetPasswordFa($plainToken));
            
            return response()->json([
                'success' => true,
                'message' => 'لینک بازیابی رمز عبور به ایمیل شما ارسال شد.'
            ]);
        } catch (Exception $e) {
            \Log::error('Failed to send password reset email: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'خطا در ارسال ایمیل. لطفاً دوباره تلاش کنید.'
            ], 500);
        }
    }

    // Password reset: perform reset with token
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $firstError = $errors->first();
            return response()->json([
                'success' => false,
                'message' => $firstError,
                'errors' => $errors
            ], 422);
        }

        // Check if user exists
        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => __('passwords.user', [], 'fa')
            ], 404);
        }

        // Check if token exists and is valid
        $passwordReset = DB::table('password_resets')
            ->where('email', $request->email)
            ->first();

        if (!$passwordReset) {
            return response()->json([
                'success' => false,
                'message' => __('passwords.token', [], 'fa')
            ], 422);
        }

        // Check if token is expired (60 minutes)
        if (now()->diffInMinutes($passwordReset->created_at) > 60) {
            return response()->json([
                'success' => false,
                'message' => 'توکن بازیابی رمز عبور منقضی شده است. لطفاً درخواست جدید ارسال کنید.'
            ], 422);
        }

        // Verify token
        if (!Hash::check($request->token, $passwordReset->token)) {
            return response()->json([
                'success' => false,
                'message' => __('passwords.token', [], 'fa')
            ], 422);
        }

        // Reset password
        try {
            $user->forceFill([
                'password' => Hash::make($request->password),
                'remember_token' => Str::random(60),
            ])->save();

            // Delete used token
            DB::table('password_resets')->where('email', $request->email)->delete();

            event(new PasswordReset($user));

            return response()->json([
                'success' => true,
                'message' => __('passwords.reset', [], 'fa')
            ]);
        } catch (Exception $e) {
            \Log::error('Failed to reset password: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'خطا در بازنشانی رمز عبور'
            ], 500);
        }
    }

    // Resend password reset email
    public function resendPasswordReset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $firstError = $errors->first();
            return response()->json([
                'success' => false,
                'message' => $firstError,
                'errors' => $errors
            ], 422);
        }

        // Check if user exists
        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'کاربری با این ایمیل در سیستم ثبت نشده است.'
            ], 404);
        }

        // Check rate limiting (prevent spam)
        $lastReset = DB::table('password_resets')
            ->where('email', $request->email)
            ->first();

        if ($lastReset && now()->diffInMinutes($lastReset->created_at) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'لطفاً قبل از درخواست مجدد ۲ دقیقه صبر کنید.'
            ], 429);
        }

        // Generate new password reset token
        $plainToken = Str::random(64);
        
        // Store hashed token in password_resets table
        DB::table('password_resets')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($plainToken),
                'created_at' => now()
            ]
        );

        // Send reset password email with plain token
        try {
            $user->notify(new ResetPasswordFa($plainToken));
            
            return response()->json([
                'success' => true,
                'message' => 'لینک بازیابی رمز عبور مجدداً به ایمیل شما ارسال شد.'
            ]);
        } catch (Exception $e) {
            \Log::error('Failed to resend password reset email: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'خطا در ارسال ایمیل. لطفاً دوباره تلاش کنید.'
            ], 500);
        }
    }

}
