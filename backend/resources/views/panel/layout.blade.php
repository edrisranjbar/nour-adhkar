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
        nav a { display: flex; align-items: center; gap: 10px; padding: 9px 12px; border-radius: 10px; color: var(--text); }
        .ic { flex: none; width: 20px; height: 20px; fill: none; stroke: currentColor; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }
        nav a .ic { color: var(--muted); transition: color .15s ease; }
        nav a:hover .ic, nav a.active .ic { color: var(--primary); }
        aside form .btn { gap: 8px; }
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
        .badge.scheduled { background: var(--primary-soft); color: var(--primary); outline: 1px dashed var(--primary); }
        .badge.draft { background: var(--warn-soft); }
        .btn { display: inline-flex; align-items: center; justify-content: center; min-height: 40px; padding: 6px 16px; border-radius: 10px; border: 1px solid var(--line); background: var(--surface); color: var(--text); font: inherit; cursor: pointer; }
        .btn.primary { background: var(--primary); border-color: var(--primary); color: #fff; }
        .btn.danger { color: var(--danger); }
        .btn.sm { min-height: 34px; padding: 2px 12px; font-size: 13px; }
        .actions { display: flex; gap: 6px; flex-wrap: wrap; }
        .actions form { margin: 0; }
        label { display: block; font-weight: 600; margin-bottom: 6px; }
        input[type=text], input[type=email], input[type=password], input[type=search], input[type=number], input[type=date], textarea, select { width: 100%; font: inherit; color: var(--text); background: var(--bg); border: 1px solid var(--line); border-radius: 10px; padding: 9px 12px; }
        textarea { min-height: 200px; resize: vertical; }
        input:focus, textarea:focus, select:focus, .btn:focus-visible { outline: 2px solid var(--primary); outline-offset: 1px; }
        .field { margin-bottom: 16px; }
        .field-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 0 14px; }
        .check { display: flex; align-items: center; gap: 8px; font-weight: 400; }
        .alert { padding: 10px 14px; border-radius: 10px; margin-bottom: 16px; background: var(--primary-soft); color: var(--primary); }
        .alert.err { background: var(--danger-soft); color: var(--danger); }
        .tabs { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 14px; }
        .tabs a { padding: 4px 14px; border-radius: 99px; border: 1px solid var(--line); color: var(--text); font-size: 14px; }
        .tabs a.active { background: var(--primary); border-color: var(--primary); color: #fff; }
        .pager { margin-top: 16px; display: flex; gap: 8px; align-items: center; }
        .empty { text-align: center; color: var(--muted); padding: 30px 0; }
        aside .close { display: none; margin-inline-start: auto; }
        .aside-head { display: flex; align-items: flex-start; gap: 8px; }
        .topbar, .backdrop { display: none; }
        .menu-btn { width: 44px; height: 44px; display: inline-grid; place-items: center; border: 1px solid var(--line); border-radius: 10px; background: var(--surface); color: var(--text); cursor: pointer; }
        .menu-btn:focus-visible { outline: 2px solid var(--primary); outline-offset: 1px; }
        .menu-btn svg { width: 22px; height: 22px; }
        @media (max-width: 860px) {
            .shell { display: block; }
            .topbar { display: flex; align-items: center; gap: 12px; position: sticky; top: 0; z-index: 20; padding: 10px 16px; background: var(--surface); border-bottom: 1px solid var(--line); }
            .topbar .brand { padding: 0; font-size: 16px; }
            .topbar .page { margin-inline-start: auto; color: var(--muted); font-size: 14px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            aside { position: fixed; inset-block: 0; inset-inline-start: 0; z-index: 40; width: min(82vw, 290px); overflow-y: auto; box-shadow: 0 0 40px rgba(0, 0, 0, .18);
                transform: translateX(100%); visibility: hidden; transition: transform .25s ease, visibility 0s linear .25s; }
            .nav-open aside { transform: none; visibility: visible; transition: transform .25s ease; }
            aside .close { display: inline-grid; }
            nav a { padding: 12px; font-size: 16px; }
            .backdrop { display: block; position: fixed; inset: 0; z-index: 30; background: rgba(0, 0, 0, .4); opacity: 0; pointer-events: none; transition: opacity .25s ease; }
            .nav-open .backdrop { opacity: 1; pointer-events: auto; }
            .nav-open { overflow: hidden; }
            main { padding: 20px 16px; }
            h1 { font-size: 20px; }
            .head > form, .head > .btn { width: 100%; }
            .head > form input { min-width: 0; }
            th, td { padding: 10px 8px; }
        }
        /* Phones: each table row becomes a card; cells are labelled from the column headers (see script). */
        @media (max-width: 640px) {
            table.stack thead { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); }
            table.stack, table.stack tbody, table.stack tr, table.stack td { display: block; width: 100%; }
            table.stack tr { padding: 10px 0; border-bottom: 1px solid var(--line); }
            table.stack tr:last-child { border-bottom: 0; }
            table.stack td { border: 0; padding: 5px 0; display: flex; flex-wrap: wrap; gap: 6px 10px; align-items: center; text-align: start; }
            table.stack td[data-label]::before { content: attr(data-label); flex: 0 0 76px; color: var(--muted); font-size: 12px; font-weight: 600; }
            table.stack td[data-label=""]::before { content: none; }
            table.stack td[dir="ltr"] { direction: rtl; text-align: start !important; }
            table.stack td > * { min-width: 0; }
            table.stack td.empty { justify-content: center; }
            .msg { max-width: none; }
        }
        @media (prefers-reduced-motion: reduce) { aside, .backdrop { transition: none !important; } }
    </style>
    @stack('head')
</head>
<body>
@auth('admin')
<div class="shell">
    <header class="topbar">
        <button class="menu-btn" type="button" aria-controls="panel-nav" aria-expanded="false" aria-label="باز کردن منو" data-nav-open>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" /></svg>
        </button>
        <div class="brand">اذکار نور</div>
        <span class="page">@yield('title', 'پنل')</span>
    </header>
    <div class="backdrop" data-nav-close></div>
    <aside id="panel-nav" aria-label="منوی مدیریت">
        <div class="aside-head">
            <div class="brand">اذکار نور<small>مدیریت برنامه</small></div>
            <button class="menu-btn close" type="button" aria-label="بستن منو" data-nav-close>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18" /></svg>
            </button>
        </div>
        <nav>
            <a href="{{ route('panel.dashboard') }}" @class(['active' => request()->routeIs('panel.dashboard')])><svg class="ic" viewBox="0 0 24 24" aria-hidden="true"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg><span>داشبورد</span></a>
            <a href="{{ route('panel.analytics') }}" @class(['active' => request()->routeIs('panel.analytics')])><svg class="ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/></svg><span>آمار</span></a>
            <a href="{{ route('panel.notices.index') }}" @class(['active' => request()->routeIs('panel.notices.*')])><svg class="ic" viewBox="0 0 24 24" aria-hidden="true"><path d="m3 11 18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/></svg><span>پیام‌ها</span></a>
            <a href="{{ route('panel.articles.index') }}" @class(['active' => request()->routeIs('panel.articles.*')])><svg class="ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h12a2 2 0 0 1 2 2v14H6a2 2 0 0 1-2-2z"/><path d="M18 8h2v10a2 2 0 0 1-2 2"/><path d="M8 8h6M8 12h6M8 16h4"/></svg><span>مقالات</span></a>
            <a href="{{ route('panel.scholars.index') }}" @class(['active' => request()->routeIs('panel.scholars.*', 'panel.lectures.*')])><svg class="ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" x2="12" y1="19" y2="22"/></svg><span>علما و سخنرانی‌ها</span></a>
            <a href="{{ route('panel.feedback.index') }}" @class(['active' => request()->routeIs('panel.feedback.*')])><svg class="ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg><span>بازخوردها</span></a>
            <a href="{{ route('panel.versions.index') }}" @class(['active' => request()->routeIs('panel.versions.*')])><svg class="ic" viewBox="0 0 24 24" aria-hidden="true"><rect width="14" height="20" x="5" y="2" rx="2"/><path d="M12 18h.01"/><path d="m9 9 3-3 3 3"/><path d="M12 6v7"/></svg><span>نسخه‌های برنامه</span></a>
            <a href="{{ route('panel.users.index') }}" @class(['active' => request()->routeIs('panel.users.*')])><svg class="ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg><span>کاربران</span></a>
            <a href="{{ route('panel.profile') }}" @class(['active' => request()->routeIs('panel.profile*')])><svg class="ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg><span>پروفایل</span></a>
        </nav>
        <form method="POST" action="{{ route('panel.logout') }}">
            @csrf
            <button class="btn sm" type="submit"><svg class="ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>خروج</button>
        </form>
    </aside>
    <main>
        @if (session('status'))
            <div class="alert" role="status">{{ session('status') }}</div>
        @endif
        @yield('content')
    </main>
</div>
<script>
    // Mobile navigation drawer: toggles body.nav-open; closes on backdrop, Escape or link tap.
    (function () {
        var body = document.body, openBtn = document.querySelector('[data-nav-open]'), aside = document.getElementById('panel-nav');
        function set(open) {
            body.classList.toggle('nav-open', open);
            openBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
            if (open) { var first = aside.querySelector('a'); if (first) first.focus(); } else { openBtn.focus({ preventScroll: true }); }
        }
        openBtn.addEventListener('click', function () { set(true); });
        document.querySelectorAll('[data-nav-close]').forEach(function (el) { el.addEventListener('click', function () { set(false); }); });
        aside.querySelectorAll('nav a').forEach(function (a) { a.addEventListener('click', function () { body.classList.remove('nav-open'); }); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && body.classList.contains('nav-open')) set(false); });

        // Label table cells with their column header so rows can stack as cards on phones.
        document.querySelectorAll('main table').forEach(function (table) {
            var heads = Array.prototype.map.call(table.querySelectorAll('thead th'), function (th) { return th.textContent.trim(); });
            if (!heads.length) return;
            table.classList.add('stack');
            table.querySelectorAll('tbody tr').forEach(function (tr) {
                Array.prototype.forEach.call(tr.children, function (td, i) { if (!td.hasAttribute('colspan')) td.setAttribute('data-label', heads[i] || ''); });
            });
        });
    })();
</script>
@else
    @yield('content')
@endauth
</body>
</html>
