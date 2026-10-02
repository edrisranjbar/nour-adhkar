@extends('panel.layout')
@section('title', 'کاربران')
@php use App\Support\PanelFormat as F; @endphp

@section('content')
<div class="head">
    <h1>کاربران</h1>
    <form method="GET" action="{{ route('panel.users.index') }}" style="display:flex;gap:6px">
        <input type="search" name="q" value="{{ $search }}" placeholder="جست‌وجوی نام یا ایمیل" aria-label="جست‌وجو">
        <button class="btn" type="submit">جست‌وجو</button>
    </form>
</div>

<div class="card table-wrap">
    <table>
        <thead><tr><th>نام</th><th>ایمیل</th><th>وضعیت</th><th>عضویت</th><th>آخرین ورود</th><th></th></tr></thead>
        <tbody>
        @forelse ($items as $user)
            <tr>
                <td>{{ $user->name }} @if ($user->role === 'admin')<span class="badge ok">مدیر</span>@endif</td>
                <td dir="ltr" style="text-align:end">{{ $user->email }}</td>
                <td>
                    @if (!$user->active)
                        <span class="badge off">غیرفعال</span>
                    @elseif ($user->email_verified_at)
                        <span class="badge ok">تأییدشده</span>
                    @else
                        <span class="badge draft">تأییدنشده</span>
                    @endif
                </td>
                <td class="muted" style="white-space:nowrap">{{ F::date($user->created_at) }}</td>
                <td class="muted" style="white-space:nowrap">{{ F::date($user->last_login_at) }}</td>
                <td>
                    @if ($user->id !== auth('admin')->id())
                        <div style="display:flex;gap:6px;flex-wrap:wrap">
                            <form method="POST" action="{{ route('panel.users.toggle', $user->id) }}" onsubmit="return confirm('وضعیت این حساب تغییر کند؟')">
                                @csrf @method('PATCH')
                                <button class="btn sm {{ $user->active ? 'danger' : '' }}" type="submit">{{ $user->active ? 'غیرفعال‌سازی' : 'فعال‌سازی' }}</button>
                            </form>
                            @if (!$user->active)
                                <form method="POST" action="{{ route('panel.users.destroy', $user->id) }}" onsubmit="return confirm('آیا از حذف این حساب کاربری و اطلاعات وابسته به آن مطمئن هستید؟ این عملیات قابل بازگشت نیست.')">
                                    @csrf @method('DELETE')
                                    <button class="btn sm danger" type="submit" aria-label="حذف حساب {{ $user->name }}">حذف</button>
                                </form>
                            @endif
                        </div>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="empty">کاربری یافت نشد.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@include('panel.pager', ['items' => $items])
@endsection
