@extends('panel.layout')
@section('title', 'بازخوردها')
@php use App\Support\PanelFormat as F; @endphp

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
                <td><div class="msg">{{ $item->message }}</div></td>
                <td class="muted" style="white-space:nowrap">{{ F::date($item->created_at) }}</td>
                <td>
                    <form method="POST" action="{{ route('panel.feedback.destroy', $item->id) }}" onsubmit="return confirm('این بازخورد حذف شود؟')">
                        @csrf @method('DELETE')
                        <button class="btn sm danger" type="submit">حذف</button>
                    </form>
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
