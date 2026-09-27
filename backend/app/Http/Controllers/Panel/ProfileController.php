<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('panel.profile', ['user' => Auth::guard('admin')->user()]);
    }

    public function updateName(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:255']);
        Auth::guard('admin')->user()->update($data);
        return back()->with('status', 'نام به‌روزرسانی شد.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password:admin',
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.current_password' => 'رمز عبور فعلی نادرست است.',
            'password.confirmed' => 'تکرار رمز عبور جدید مطابقت ندارد.',
            'password.min' => 'رمز عبور جدید باید دست‌کم ۸ نویسه باشد.',
        ]);

        Auth::guard('admin')->user()->update(['password' => $request->input('password')]);
        $request->session()->regenerate();

        return back()->with('status', 'رمز عبور تغییر کرد.');
    }
}
