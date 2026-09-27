<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q'));
        $items = User::query()
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")))
            ->orderByDesc('created_at')
            ->paginate(30)
            ->withQueryString();

        return view('panel.users.index', compact('items', 'search'));
    }

    public function toggle(int $id)
    {
        $user = User::findOrFail($id);
        if ($user->id === Auth::guard('admin')->id()) {
            return back()->with('status', 'نمی‌توانید حساب خودتان را غیرفعال کنید.');
        }
        $user->update(['active' => !$user->active]);
        return back()->with('status', $user->active ? 'حساب فعال شد.' : 'حساب غیرفعال شد.');
    }
}
