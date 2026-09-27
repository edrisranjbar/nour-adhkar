@extends('panel.layout')
@section('title', 'داشبورد')
@php use App\Support\PanelFormat as F; use App\Http\Controllers\Panel\FeedbackController; @endphp

@section('content')
<div class="head"><h1>داشبورد</h1></div>

<div class="grid">
    <div class="card stat"><div class="n">{{ F::number($stats['users']) }}</div><div class="l">کاربران · {{ F::number($stats['usersWeek']) }} در هفته اخیر</div></div>
    <div class="card stat"><div class="n">{{ F::number($stats['verified']) }}</div><div class="l">ایمیل تأییدشده</div></div>
    <div class="card stat"><div class="n">{{ F::number($stats['feedback']) }}</div><div class="l">بازخورد · {{ F::number($stats['feedbackWeek']) }} در هفته اخیر</div></div>
    <div class="card stat"><div class="n">{{ F::number($stats['notices']) }}</div><div class="l">پیام منتشرشده · {{ F::number($stats['drafts']) }} پیش‌نویس</div></div>
</div>

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
