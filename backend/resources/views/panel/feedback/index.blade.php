@extends('panel.layout')
@section('title', 'بازخوردها')
@php use App\Support\PanelFormat as F; @endphp

@section('content')
<div class="head"><h1>بازخوردهای برنامه</h1></div>

<div class="tabs">
    <a href="{{ route('panel.feedback.index') }}" @class(['active' => !isset($types[$type ?? ''])])>همه</a>
    @foreach ($types as $key => $label)
        <a href="{{ route('panel.feedback.index', ['type' => $key]) }}" @class(['active' => $type === $key])>{{ $label }}</a>
    @endforeach
</div>

<div class="card table-wrap">
    <table>
        <thead><tr><th>نوع</th><th>متن</th><th>زمان</th><th></th></tr></thead>
        <tbody>
        @forelse ($items as $item)
            <tr>
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
            <tr><td colspan="4" class="empty">بازخوردی یافت نشد.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@include('panel.pager', ['items' => $items])
@endsection
