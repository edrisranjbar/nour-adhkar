<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'پنل') · مدیریت اذکار نور</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/vazirmatn@33.0.3/Vazirmatn-font-face.css">
    <style>
        :root {
            --bg: #f5f6f4; --surface: #fff; --text: #1d2420; --muted: #66716b; --line: #e3e7e4;
            --primary: #1f7a57; --primary-soft: #e4f2ec; --danger: #b3261e; --danger-soft: #fbe9e7;
            --warn-soft: #fff4dc; --radius: 12px;
        }
        @media (prefers-color-scheme: dark) {
            :root {
                --bg: #111513; --surface: #1a201d; --text: #e6ebe8; --muted: #9aa59f; --line: #2a322e;
                --primary: #5cc596; --primary-soft: #1d3329; --danger: #f2b8b5; --danger-soft: #3a1f1d;
                --warn-soft: #3a3120;
            }
        }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Vazirmatn, Tahoma, sans-serif; background: var(--bg); color: var(--text); font-size: 15px; line-height: 1.8; }
        a { color: var(--primary); text-decoration: none; }
        .shell { display: flex; min-height: 100vh; }
        aside { width: 230px; flex-shrink: 0; background: var(--surface); border-inline-end: 1px solid var(--line); padding: 20px 14px; display: flex; flex-direction: column; gap: 4px; }
        .brand { font-weight: 800; font-size: 18px; padding: 0 10px 16px; }
        .brand small { display: block; font-weight: 400; font-size: 12px; color: var(--muted); }
        nav a { display: block; padding: 9px 12px; border-radius: 10px; color: var(--text); }
        nav a:hover { background: var(--bg); }
        nav a.active { background: var(--primary-soft); color: var(--primary); font-weight: 700; }
        aside form { margin-top: auto; }
        main { flex: 1; min-width: 0; padding: 28px clamp(16px, 4vw, 40px); }
        .head { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 20px; }
        h1 { font-size: 22px; margin: 0; }
        .card { background: var(--surface); border: 1px solid var(--line); border-radius: var(--radius); padding: 18px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 14px; margin-bottom: 20px; }
        .stat .n { font-size: 28px; font-weight: 800; }
        .stat .l { color: var(--muted); font-size: 13px; }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: start; padding: 12px 10px; border-bottom: 1px solid var(--line); vertical-align: top; }
        th { color: var(--muted); font-weight: 600; font-size: 13px; }
        tr:last-child td { border-bottom: 0; }
        .msg { white-space: pre-wrap; word-break: break-word; max-width: 560px; }
        .muted { color: var(--muted); font-size: 13px; }
        .badge { display: inline-block; padding: 1px 10px; border-radius: 99px; font-size: 12px; background: var(--bg); white-space: nowrap; }
        .badge.ok { background: var(--primary-soft); color: var(--primary); }
        .badge.off { background: var(--danger-soft); color: var(--danger); }
        .badge.draft { background: var(--warn-soft); }
        .btn { display: inline-flex; align-items: center; justify-content: center; min-height: 40px; padding: 6px 16px; border-radius: 10px; border: 1px solid var(--line); background: var(--surface); color: var(--text); font: inherit; cursor: pointer; }
        .btn.primary { background: var(--primary); border-color: var(--primary); color: #fff; }
        .btn.danger { color: var(--danger); }
        .btn.sm { min-height: 34px; padding: 2px 12px; font-size: 13px; }
        .actions { display: flex; gap: 6px; flex-wrap: wrap; }
        .actions form { margin: 0; }
        label { display: block; font-weight: 600; margin-bottom: 6px; }
        input[type=text], input[type=email], input[type=password], input[type=search], textarea, select { width: 100%; font: inherit; color: var(--text); background: var(--bg); border: 1px solid var(--line); border-radius: 10px; padding: 9px 12px; }
        textarea { min-height: 200px; resize: vertical; }
        input:focus, textarea:focus, select:focus, .btn:focus-visible { outline: 2px solid var(--primary); outline-offset: 1px; }
        .field { margin-bottom: 16px; }
        .check { display: flex; align-items: center; gap: 8px; font-weight: 400; }
        .alert { padding: 10px 14px; border-radius: 10px; margin-bottom: 16px; background: var(--primary-soft); color: var(--primary); }
        .alert.err { background: var(--danger-soft); color: var(--danger); }
        .tabs { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 14px; }
        .tabs a { padding: 4px 14px; border-radius: 99px; border: 1px solid var(--line); color: var(--text); font-size: 14px; }
        .tabs a.active { background: var(--primary); border-color: var(--primary); color: #fff; }
        .pager { margin-top: 16px; display: flex; gap: 8px; align-items: center; }
        .empty { text-align: center; color: var(--muted); padding: 30px 0; }
        @media (max-width: 760px) {
            .shell { flex-direction: column; }
            aside { width: auto; flex-direction: row; flex-wrap: wrap; align-items: center; border-inline-end: 0; border-bottom: 1px solid var(--line); padding: 10px 16px; }
            .brand { padding: 0 6px 0 12px; font-size: 16px; }
            .brand small { display: none; }
            nav { display: flex; flex-wrap: wrap; gap: 2px; }
            nav a { padding: 6px 10px; }
            aside form { margin: 0 auto 0 0; }
            main { padding: 20px 16px; }
        }
    </style>
    @stack('head')
</head>
<body>
@auth('admin')
<div class="shell">
    <aside>
        <div class="brand">اذکار نور<small>مدیریت برنامه</small></div>
        <nav>
            <a href="{{ route('panel.dashboard') }}" @class(['active' => request()->routeIs('panel.dashboard')])>داشبورد</a>
            <a href="{{ route('panel.analytics') }}" @class(['active' => request()->routeIs('panel.analytics')])>آمار</a>
            <a href="{{ route('panel.notices.index') }}" @class(['active' => request()->routeIs('panel.notices.*')])>پیام‌ها</a>
            <a href="{{ route('panel.scholars.index') }}" @class(['active' => request()->routeIs('panel.scholars.*', 'panel.lectures.*')])>علما و سخنرانی‌ها</a>
            <a href="{{ route('panel.feedback.index') }}" @class(['active' => request()->routeIs('panel.feedback.*')])>بازخوردها</a>
            <a href="{{ route('panel.users.index') }}" @class(['active' => request()->routeIs('panel.users.*')])>کاربران</a>
            <a href="{{ route('panel.profile') }}" @class(['active' => request()->routeIs('panel.profile*')])>پروفایل</a>
        </nav>
        <form method="POST" action="{{ route('panel.logout') }}">
            @csrf
            <button class="btn sm" type="submit">خروج</button>
        </form>
    </aside>
    <main>
        @if (session('status'))
            <div class="alert" role="status">{{ session('status') }}</div>
        @endif
        @yield('content')
    </main>
</div>
@else
    @yield('content')
@endauth
</body>
</html>
