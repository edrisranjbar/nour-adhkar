@extends('panel.layout')
@section('title', 'بازخوردها')
@php use App\Support\PanelFormat as F; @endphp

@push('head')
<style>
    .fb-reply { margin-top: 8px; padding: 8px 12px; border-inline-start: 3px solid var(--primary); background: var(--primary-soft); border-radius: 8px; }
    .fb-reply-form { margin-top: 8px; }
    .fb-reply-form summary { cursor: pointer; color: var(--primary); font-size: 13px; width: fit-content; }
    .fb-reply-form textarea { margin-top: 6px; }
    .btn.liked { color: #d23c5a; border-color: color-mix(in srgb, #d23c5a 40%, var(--line)); }
</style>
@endpush

@section('content')
<div class="head"><h1>بازخوردها</h1></div>
<div class="tabs" aria-label="منبع بازخورد">
    <a href="{{ route('panel.feedback.index') }}" @class(['active' => $source === 'all'])>همهٔ منابع</a>
    <a href="{{ route('panel.feedback.index', ['source' => 'app']) }}" @class(['active' => $source === 'app'])>درون برنامه</a>
    <a href="{{ route('panel.feedback.index', ['source' => 'bazaar']) }}" @class(['active' => $source === 'bazaar'])>کافه‌بازار</a>
</div>

@if ($source !== 'app')
    <section class="card" style="margin-bottom:18px">
        @include('panel.feedback._bazaar')
        @include('panel.pager', ['items' => $bazaarItems])
    </section>
@endif

@if ($source !== 'bazaar')
<h2 style="font-size:17px">بازخوردهای درون برنامه</h2>

<div class="tabs">
    <a href="{{ route('panel.feedback.index', ['source' => $source]) }}" @class(['active' => !isset($types[$type ?? ''])])>همهٔ انواع</a>
    @foreach ($types as $key => $label)
        <a href="{{ route('panel.feedback.index', ['source' => $source, 'type' => $key]) }}" @class(['active' => $type === $key])>{{ $label }}</a>
    @endforeach
</div>

<div class="card table-wrap">
    <table>
        <thead><tr><th>فرستنده</th><th>نوع</th><th>متن</th><th>زمان</th><th></th></tr></thead>
        <tbody>
        @forelse ($items as $item)
            <tr>
                <td style="white-space:nowrap">@include('panel.feedback._sender', ['item' => $item])</td>
                <td><span class="badge">{{ $types[$item->type] ?? $item->type }}</span></td>
                <td>
                    <div class="msg">{{ $item->message }}</div>
                    @if ($item->reply)
                        <div class="fb-reply"><span class="muted">پاسخ شما · {{ F::date($item->replied_at) }}</span><div class="msg">{{ $item->reply }}</div></div>
                    @endif
                    @if ($item->user_id)
                        <details class="fb-reply-form">
                            <summary>{{ $item->reply ? 'ویرایش پاسخ' : 'پاسخ دادن' }}</summary>
                            <form method="POST" action="{{ route('panel.feedback.reply', $item->id) }}">
                                @csrf @method('PUT')
                                <textarea name="reply" maxlength="3000" style="min-height:90px" placeholder="پاسخ شما در «پیام‌های من» برای فرستنده نمایش داده می‌شود.">{{ $item->reply }}</textarea>
                                <div class="actions" style="margin-top:6px">
                                    <button class="btn sm primary" type="submit">ثبت پاسخ</button>
                                    @if ($item->reply)<button class="btn sm danger" type="submit" name="reply" value="">حذف پاسخ</button>@endif
                                </div>
                            </form>
                        </details>
                    @else
                        <div class="muted" style="font-size:12px;margin-top:4px">فرستنده ناشناس است؛ پاسخ و پسند به او نمی‌رسد.</div>
                    @endif
                </td>
                <td class="muted" style="white-space:nowrap">{{ F::date($item->created_at) }}</td>
                <td>
                    <div class="actions">
                        @if ($item->user_id)
                            <form method="POST" action="{{ route('panel.feedback.like', $item->id) }}">
                                @csrf @method('PATCH')
                                <button class="btn sm {{ $item->liked_at ? 'liked' : '' }}" type="submit" aria-pressed="{{ $item->liked_at ? 'true' : 'false' }}"
                                        title="{{ $item->liked_at ? 'برداشتن پسند' : 'پسندیدن' }}">{{ $item->liked_at ? '♥ پسندیده' : '♡ پسندیدن' }}</button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('panel.feedback.destroy', $item->id) }}" onsubmit="return confirm('این بازخورد حذف شود؟')">
                            @csrf @method('DELETE')
                            <button class="btn sm danger" type="submit">حذف</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="empty">بازخوردی یافت نشد.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@include('panel.pager', ['items' => $items])
@endif
@endsection
