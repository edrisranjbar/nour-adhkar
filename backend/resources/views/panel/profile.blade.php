@extends('panel.layout')
@section('title', 'پروفایل')

@section('content')
<div class="head"><h1>پروفایل من</h1></div>

<div style="display:grid;gap:16px;max-width:560px">
    <form class="card" method="POST" action="{{ route('panel.profile.name') }}">
        @csrf @method('PUT')
        <h2 style="font-size:17px;margin:0 0 12px">مشخصات</h2>
        @error('name')<div class="alert err" role="alert">{{ $message }}</div>@enderror
        <div class="field">
            <label for="name">نام</label>
            <input id="name" type="text" name="name" maxlength="255" required value="{{ old('name', $user->name) }}">
        </div>
        <div class="field">
            <label for="email">ایمیل</label>
            <input id="email" type="email" value="{{ $user->email }}" dir="ltr" disabled>
        </div>
        <button class="btn primary" type="submit">ذخیره نام</button>
    </form>

    <form class="card" method="POST" action="{{ route('panel.profile.password') }}">
        @csrf @method('PUT')
        <h2 style="font-size:17px;margin:0 0 12px">تغییر رمز عبور</h2>
        @if ($errors->hasAny(['current_password', 'password']))
            <div class="alert err" role="alert">{{ $errors->first('current_password') ?: $errors->first('password') }}</div>
        @endif
        <div class="field">
            <label for="current_password">رمز عبور فعلی</label>
            <input id="current_password" type="password" name="current_password" dir="ltr" required autocomplete="current-password">
        </div>
        <div class="field">
            <label for="password">رمز عبور جدید</label>
            <input id="password" type="password" name="password" dir="ltr" required minlength="8" autocomplete="new-password">
        </div>
        <div class="field">
            <label for="password_confirmation">تکرار رمز عبور جدید</label>
            <input id="password_confirmation" type="password" name="password_confirmation" dir="ltr" required minlength="8" autocomplete="new-password">
        </div>
        <button class="btn primary" type="submit">تغییر رمز عبور</button>
    </form>
</div>
@endsection
