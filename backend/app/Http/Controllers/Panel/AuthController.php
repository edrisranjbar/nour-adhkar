<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function show()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('panel.dashboard');
        }
        return view('panel.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $ok = Auth::guard('admin')->attempt(
            $credentials + ['role' => 'admin', 'active' => true],
            $request->boolean('remember')
        );
        if (!$ok) {
            return back()->withErrors(['email' => 'ایمیل یا رمز عبور نادرست است.'])->onlyInput('email');
        }

        $request->session()->regenerate();
        return redirect()->intended(route('panel.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('panel.login');
    }
}
