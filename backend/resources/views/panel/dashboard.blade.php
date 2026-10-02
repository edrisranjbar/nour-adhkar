@extends('panel.layout')
@section('title', 'داشبورد')
@php use App\Support\PanelFormat as F; use App\Http\Controllers\Panel\FeedbackController; @endphp

@push('head')
<style>
    .kpis { display: grid; grid-template-columns: repeat(auto-fit, minmax(188px, 1fr)); gap: 14px; margin-bottom: 18px; }
    .kpi { --accent: var(--primary); display: flex; align-items: center; gap: 12px; padding: 16px; background: var(--surface); border: 1px solid var(--line); border-radius: 16px; transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease; }
    .kpi:hover { transform: translateY(-2px); border-color: color-mix(in srgb, var(--accent) 45%, var(--line)); box-shadow: 0 8px 22px -14px var(--accent); }
    .kpi-ic { flex: none; width: 48px; height: 48px; display: grid; place-items: center; border-radius: 14px; color: var(--accent); background: color-mix(in srgb, var(--accent) 14%, transparent); }
    .kpi-ic svg { width: 24px; height: 24px; fill: none; stroke: currentColor; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }
    .kpi-body { min-width: 0; display: flex; flex-direction: column; }
    .kpi-l { color: var(--muted); font-size: 13px; line-height: 1.5; }
    .kpi-n { font-size: 28px; font-weight: 800; line-height: 1.3; font-variant-numeric: tabular-nums; }
    .kpi-s { color: var(--muted); font-size: 12px; line-height: 1.5; }
    .feedback-kpi { gap: 8px; }
    .feedback-kpi .kpi-ic { width: 32px; height: 32px; }
    .feedback-kpi .kpi-body { flex: 1; }
    .feedback-counts { display: flex; align-items: baseline; gap: 6px; font-size: 24px; white-space: nowrap; }
    .feedback-counts small { font-size: 10px; font-weight: 400; color: var(--muted); }
    .feedback-rating { display: flex; align-items: center; gap: 5px; min-height: 18px; }
    .rating-stars { position: relative; display: inline-flex; width: 60px; height: 12px; direction: ltr; color: var(--line); flex: none; }
    .rating-stars svg { width: 60px; height: 12px; fill: currentColor; }
    .rating-stars-fill { position: absolute; inset: 0 auto 0 0; width: var(--rating-fill, 0%); overflow: hidden; color: #e08a1e; }
    .feedback-rating.stale { opacity: .6; }
    @media (max-width: 560px) {
        .kpis { grid-template-columns: repeat(2, 1fr); gap: 10px; }
        .kpi { flex-direction: column; align-items: flex-start; gap: 10px; padding: 14px; }
        .kpi-n { font-size: 24px; }
    }
    @media (prefers-reduced-motion: reduce) { .kpi { transition: none; } .kpi:hover { transform: none; } }

    .dash-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 14px; margin-bottom: 18px; }
    .dash-grid .card h2 { font-size: 17px; margin: 0; }
    .list-row { display: flex; align-items: center; gap: 10px; padding: 10px 0; border-top: 1px solid var(--line); min-width: 0; }
    .list-row .grow { flex: 1; min-width: 0; }
    .list-row .grow > * { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .avatar { flex: none; width: 34px; height: 34px; border-radius: 50%; display: grid; place-items: center; font-weight: 700; font-size: 14px;
        background: var(--primary-soft); color: var(--primary); }
    .ai-progress { margin: 10px 0 14px; }
    .ai-progress .track { height: 8px; border-radius: 99px; background: var(--line); overflow: hidden; }
    .ai-progress .fill { height: 100%; border-radius: inherit; background: var(--primary); }
    .ai-progress .meta { display: flex; justify-content: space-between; gap: 8px; margin-top: 6px; font-size: 13px; color: var(--muted); }
    .ai-counts { display: flex; gap: 8px; flex-wrap: wrap; }
    .pulse { width: 8px; height: 8px; border-radius: 50%; background: #e08a1e; flex: none; animation: pulse 1.4s ease-in-out infinite; }
    @keyframes pulse { 50% { opacity: .3; } }
    @media (prefers-reduced-motion: reduce) { .pulse { animation: none; } }
</style>
@endpush

@section('content')
<div class="head"><h1>داشبورد</h1></div>

<div class="kpis">
    <div class="kpi" style="--accent:#2f9e6b">
        <span class="kpi-ic" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span>
        <div class="kpi-body">
            <div class="kpi-l">کاربران</div>
            <div class="kpi-n">{{ F::number($stats['users']) }}</div>
            <div class="kpi-s">{{ F::number($stats['usersWeek']) }} در هفته اخیر</div>
        </div>
    </div>
    <div class="kpi" style="--accent:#3b82f6">
        <span class="kpi-ic" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z"/><path d="m9 12 2 2 4-4"/></svg></span>
        <div class="kpi-body">
            <div class="kpi-l">ایمیل تأییدشده</div>
            <div class="kpi-n">{{ F::number($stats['verified']) }}</div>
            <div class="kpi-s">{{ F::digits($stats['users'] > 0 ? (int) round($stats['verified'] * 100 / $stats['users']) : 0) }}٪ از کاربران</div>
        </div>
    </div>
    <div class="kpi feedback-kpi" style="--accent:#e08a1e">
        <span class="kpi-ic" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></span>
        <div class="kpi-body">
            <div class="kpi-l">بازخورد</div>
            <div class="kpi-n feedback-counts">
                <span title="بازخورد درون‌برنامه"><span id="feedback-count">{{ F::number($stats['feedback']) }}</span> <small>برنامه</small></span>
                <span title="رأی بازار"><span id="bazaar-votes">—</span> <small>بازار</small></span>
            </div>
            <div class="kpi-s feedback-rating" id="bazaar-rating-status" role="img" aria-label="امتیاز بازار در حال دریافت" title="امتیاز بازار در حال دریافت">
                <span class="rating-stars" id="bazaar-stars" aria-hidden="true">
                    <svg viewBox="0 0 120 24" xmlns="http://www.w3.org/2000/svg"><defs><path id="bazaar-star" d="m12 2 3.1 6.3 6.9 1-5 4.9 1.2 6.8-6.2-3.2-6.2 3.2L7 14.2l-5-4.9 6.9-1z"/></defs><use href="#bazaar-star"/><use href="#bazaar-star" x="24"/><use href="#bazaar-star" x="48"/><use href="#bazaar-star" x="72"/><use href="#bazaar-star" x="96"/></svg>

                    <span class="rating-stars-fill"><svg viewBox="0 0 120 24" aria-hidden="true"><use href="#bazaar-star"/><use href="#bazaar-star" x="24"/><use href="#bazaar-star" x="48"/><use href="#bazaar-star" x="72"/><use href="#bazaar-star" x="96"/></svg></span>
                </span>
                <span id="bazaar-rating">—</span>
            </div>
        </div>
    </div>
    <div class="kpi" style="--accent:#8b5cf6">
        <span class="kpi-ic" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg></span>
        <div class="kpi-body">
            <div class="kpi-l">پیام منتشرشده</div>
            <div class="kpi-n">{{ F::number($stats['notices']) }}</div>
            <div class="kpi-s">{{ F::number($stats['reads']) }} بار خوانده‌شدن</div>
        </div>
    </div>
</div>

@include('panel._installs')

<div class="dash-grid">
    <div class="card">
        <div class="head" style="margin-bottom:6px">
            <h2>کاربران تازه</h2>
            <a href="{{ route('panel.users.index') }}">همه</a>
        </div>
        @forelse ($recentUsers as $user)
            <div class="list-row">
                <span class="avatar" aria-hidden="true">{{ mb_substr(trim($user->name ?: $user->email), 0, 1) }}</span>
                <div class="grow">
                    <div><strong>{{ $user->name ?: '—' }}</strong></div>
                    <div class="muted" dir="ltr" style="text-align:start">{{ $user->email }}</div>
                </div>
                <div style="text-align:end;flex:none">
                    @if ($user->email_verified_at)
                        <span class="badge ok">تأییدشده</span>
                    @else
                        <span class="badge draft">تأییدنشده</span>
                    @endif
                    <div class="muted">{{ F::date($user->created_at) }}</div>
                </div>
            </div>
        @empty
            <div class="empty">هنوز کاربری ثبت‌نام نکرده است.</div>
        @endforelse
    </div>

    <div class="card">
        <div class="head" style="margin-bottom:0">
            <h2>توضیح سخنرانی‌ها از روی صوت</h2>
            <a href="{{ route('panel.scholars.index') }}">سخنرانی‌ها</a>
        </div>
        @php($pct = $ai['total'] > 0 ? (int) round($ai['done'] * 100 / $ai['total']) : 0)
        <div class="ai-progress">
            <div class="track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $pct }}" aria-label="پیشرفت کل">
                <div class="fill" style="width:{{ $pct }}%"></div>
            </div>
            <div class="meta">
                <span>{{ F::number($ai['done']) }} از {{ F::number($ai['total']) }} سخنرانی پردازش شده</span>
                <span>{{ F::digits($pct) }}٪</span>
            </div>
        </div>
        <div class="ai-counts">
            <span class="badge draft">در حال پردازش: {{ F::number($ai['processing']) }}</span>
            <span class="badge">در صف: {{ F::number($ai['queued']) }}</span>
            @if ($ai['failed'])
                <span class="badge off">ناموفق: {{ F::number($ai['failed']) }}</span>
            @endif
        </div>
        @forelse ($ai['active'] as $lecture)
            <div class="list-row">
                @if ($lecture->ai_status === 'processing')
                    <span class="pulse" aria-hidden="true"></span>
                @endif
                <div class="grow">
                    <div><a href="{{ route('panel.lectures.edit', [$lecture->scholar_id, $lecture->id]) }}"><strong>{{ $lecture->title }}</strong></a></div>
                    <div class="muted">
                        {{ $lecture->scholar }}
                        @if ($lecture->ai_status === 'failed' && $lecture->ai_error) · <span dir="auto">{{ \Illuminate\Support\Str::limit($lecture->ai_error, 80) }}</span>@endif
                    </div>
                </div>
                <div style="text-align:end;flex:none">
                    @switch($lecture->ai_status)
                        @case('processing') <span class="badge draft">تبدیل به متن</span> @break
                        @case('queued') <span class="badge">در صف</span> @break
                        @default <span class="badge off">ناموفق</span>
                    @endswitch
                    <div class="muted">{{ F::date($lecture->ai_claimed_at ?? $lecture->updated_at) }}</div>
                </div>
            </div>
        @empty
            <div class="empty" style="padding:18px 0 4px">اکنون سخنرانی‌ای در حال پردازش نیست.</div>
        @endforelse
    </div>
</div>

<div class="card">
    <div class="head" style="margin-bottom:6px">
        <h2 style="font-size:17px;margin:0">آخرین بازخوردهای درون برنامه</h2>
        <a href="{{ route('panel.feedback.index') }}">همه</a>
    </div>
    @forelse ($latestFeedback as $item)
        <div style="padding:10px 0;border-top:1px solid var(--line)">
            <span class="badge">{{ FeedbackController::TYPES[$item->type] ?? $item->type }}</span>
            <span class="muted">{{ F::date($item->created_at) }}</span>
            <div class="msg">{{ \Illuminate\Support\Str::limit($item->message, 220) }}</div>
        </div>
    @empty
        <div class="empty">هنوز بازخوردی نرسیده است.</div>
    @endforelse
</div>
<section class="card" id="bazaar-reviews" style="margin-top:18px">
    @include('panel.feedback._bazaar', ['compact' => true])
</section>
@endsection
