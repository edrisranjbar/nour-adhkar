<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\EmailVerificationService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/** App email verification: confirm a 5-digit code (then sign in) or request a new code. */
class EmailVerificationController extends Controller
{
    public function __construct(private EmailVerificationService $codes)
    {
    }

    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'code' => ['required', 'string', 'regex:/^\d{5}$/'],
            'password' => 'required|string',
        ], [
            'code.regex' => 'کد تأیید باید ۵ رقم باشد.',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        // The password is required too, so a guessed code alone never grants access.
        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'ایمیل یا رمز عبور نادرست است.'], 401);
        }
        if (!$user->active) {
            return response()->json([
                'success' => false,
                'message' => 'حساب کاربری شما غیرفعال شده است. لطفاً با پشتیبانی تماس بگیرید.'
            ], 403);
        }
        // Never issue a token for an already-verified account here: that would let anyone
        // sign in with an email and arbitrary digits. Verified users use the password login.
        if ($user->email_verified_at !== null) {
            return response()->json([
                'success' => false,
                'message' => 'این ایمیل قبلاً تأیید شده است. با رمز عبور وارد شوید.'
            ], 409);
        }
        $result = $this->codes->verify($user, $request->code);
        if ($result !== 'ok') {
            $messages = [
                'invalid' => 'کد تأیید نادرست است.',
                'expired' => 'کد تأیید منقضی شده است. کد جدید درخواست کنید.',
                'too_many' => 'تلاش‌های نادرست زیاد بود. کد جدید درخواست کنید.',
            ];
            return response()->json(['success' => false, 'message' => $messages[$result]], 422);
        }

        $token = auth()->login($user);
        $user->update(['last_login_at' => now()]);

        return response()->json([
            'success' => true,
            'user' => new UserResource($user),
            'token' => $token,
            'message' => 'ایمیل شما تأیید شد'
        ]);
    }

    public function resend(Request $request)
    {
        $validator = Validator::make($request->all(), ['email' => 'required|email']);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        // Same response whether or not the account exists, so emails cannot be probed.
        $user = User::where('email', $request->email)->first();
        if ($user && $user->email_verified_at === null) {
            $wait = $this->codes->cooldownRemaining($user->email);
            if ($wait > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'لطفاً کمی صبر کنید و دوباره تلاش کنید.',
                    'retry_after' => $wait,
                ], 429);
            }
            try {
                $this->codes->send($user);
            } catch (Exception $e) {
                \Log::error('Failed to send verification code: ' . $e->getMessage());
                return response()->json(['success' => false, 'message' => 'ارسال ایمیل انجام نشد. لطفاً دوباره تلاش کنید.'], 500);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'اگر این ایمیل نیاز به تأیید داشته باشد، کد جدید ارسال شد.',
            'retry_after' => EmailVerificationService::RESEND_COOLDOWN_SECONDS,
        ]);
    }
}
