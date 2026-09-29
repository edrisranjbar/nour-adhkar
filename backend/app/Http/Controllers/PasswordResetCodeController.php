<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\EmailVerificationService;
use App\Services\PasswordResetCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * App password reset with a 5-digit emailed code: request a code, then confirm it together
 * with a new password to set the password and sign in. (The website keeps its link-based flow.)
 */
class PasswordResetCodeController extends Controller
{
    public function __construct(private PasswordResetCodeService $codes)
    {
    }

    public function request(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), ['email' => 'required|email']);
            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }

            // Same answer whether or not the account exists, so emails cannot be probed.
            $user = User::where('email', $request->email)->first();
            if ($user && $user->active) {
                $wait = $this->codes->cooldownRemaining($user->email);
                if ($wait > 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'لطفاً کمی صبر کنید و دوباره تلاش کنید.',
                        'retry_after' => $wait,
                    ], 429);
                }
                $this->codes->send($user);
            }

            return response()->json([
                'success' => true,
                'message' => 'اگر حسابی با این ایمیل وجود داشته باشد، کد بازیابی به آن فرستاده شد.',
                'retry_after' => EmailVerificationService::RESEND_COOLDOWN_SECONDS,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Password reset code request failed: ' . $e->getMessage());
            return $this->serverError();
        }
    }

    public function confirm(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'code' => ['required', 'string', 'regex:/^\d{5}$/'],
                'password' => 'required|string|min:6',
            ], [
                'code.regex' => 'کد بازیابی باید ۵ رقم باشد.',
                'password.min' => 'رمز عبور تازه باید حداقل ۶ کاراکتر باشد.',
            ]);
            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }

            $user = User::where('email', $request->email)->first();
            if (!$user) {
                // Do not reveal whether the account exists.
                return response()->json(['success' => false, 'message' => 'کد بازیابی نادرست است.'], 422);
            }
            if (!$user->active) {
                return response()->json([
                    'success' => false,
                    'message' => 'حساب کاربری شما غیرفعال شده است. لطفاً با پشتیبانی تماس بگیرید.'
                ], 403);
            }

            $result = $this->codes->verify($user, $request->code);
            if ($result !== 'ok') {
                $messages = [
                    'invalid' => 'کد بازیابی نادرست است.',
                    'expired' => 'کد بازیابی منقضی شده است. کد جدید درخواست کنید.',
                    'too_many' => 'تلاش‌های نادرست زیاد بود. کد جدید درخواست کنید.',
                ];
                return response()->json(['success' => false, 'message' => $messages[$result]], 422);
            }

            $user->forceFill(['password' => Hash::make($request->password)])->save();
            $token = auth()->login($user);
            $user->update(['last_login_at' => now()]);

            return response()->json([
                'success' => true,
                'user' => new UserResource($user),
                'token' => $token,
                'message' => 'رمز عبور شما تغییر کرد'
            ]);
        } catch (\Throwable $e) {
            \Log::error('Password reset confirm failed: ' . $e->getMessage());
            return $this->serverError();
        }
    }

    private function serverError()
    {
        return response()->json([
            'success' => false,
            'message' => 'مشکلی در سرور پیش آمده است. لطفاً چند دقیقه دیگر دوباره تلاش کنید.'
        ], 500);
    }
}
