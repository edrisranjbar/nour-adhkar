<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PanelAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::guard('admin')->user();
        if (!$user || $user->role !== 'admin' || !$user->active) {
            Auth::guard('admin')->logout();
            return redirect()->guest(route('panel.login'));
        }

        return $next($request);
    }
}
