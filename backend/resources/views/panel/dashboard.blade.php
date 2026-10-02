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

<div class="card">
    <div class="head" style="margin-bottom:6px">
        <h2 style="font-size:17px;margin:0">آخرین بازخوردها</h2>
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
@endsection
