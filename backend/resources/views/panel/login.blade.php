@extends('panel.layout')
@section('title', 'ورود')

@section('content')
<main style="display:grid;place-items:center;min-height:100vh;padding:16px">
    <form class="card" method="POST" action="{{ route('panel.login') }}" style="width:100%;max-width:380px">
        @csrf
        <h1 style="margin-bottom:4px">ورود به پنل</h1>
        <p class="muted" style="margin-top:0">مدیریت برنامه اذکار نور</p>

        @if ($errors->any())
            <div class="alert err" role="alert">{{ $errors->first() }}</div>
        @endif

        <div class="field">
            <label for="email">ایمیل</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" dir="ltr" required autofocus autocomplete="username">
        </div>
        <div class="field">
            <label for="password">رمز عبور</label>
            <input id="password" type="password" name="password" dir="ltr" required autocomplete="current-password">
        </div>
        <div class="field">
            <label class="check"><input type="checkbox" name="remember" value="1"> مرا به خاطر بسپار</label>
        </div>
        <button class="btn primary" type="submit" style="width:100%">ورود</button>
    </form>
</main>
@endsection
