@extends('panel.layout')
@section('title', 'پیام‌ها')
@php use App\Support\PanelFormat as F; @endphp

@section('content')
<div class="head">
    <h1>پیام‌های داخل برنامه</h1>
    <a class="btn primary" href="{{ route('panel.notices.create') }}">پیام جدید</a>
</div>

<div class="card table-wrap">
    <table>
        <thead><tr><th>عنوان</th><th>وضعیت</th><th>خوانده‌شده</th><th>زمان</th><th></th></tr></thead>
        <tbody>
        @forelse ($items as $item)
            <tr>
                <td>
                    <strong>{{ $item->title }}</strong>
                    <div class="muted msg">{{ \Illuminate\Support\Str::limit($item->message, 140) }}</div>
                </td>
                <td>
                    @if ($item->published)
                        <span class="badge ok">منتشرشده</span>
                    @else
                        <span class="badge draft">پیش‌نویس</span>
                    @endif
                </td>
                <td>{{ F::number($item->reads_count) }}</td>
                <td class="muted" style="white-space:nowrap">{{ F::date($item->created_at) }}</td>
                <td>
                    <div class="actions">
                        <a class="btn sm" href="{{ route('panel.notices.edit', $item->id) }}">ویرایش</a>
                        <form method="POST" action="{{ route('panel.notices.toggle', $item->id) }}">
                            @csrf @method('PATCH')
                            <button class="btn sm" type="submit">{{ $item->published ? 'لغو انتشار' : 'انتشار' }}</button>
                        </form>
                        <form method="POST" action="{{ route('panel.notices.destroy', $item->id) }}" onsubmit="return confirm('این پیام برای همه کاربران حذف شود؟')">
                            @csrf @method('DELETE')
                            <button class="btn sm danger" type="submit">حذف</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="empty">هنوز پیامی ساخته نشده است.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@include('panel.pager', ['items' => $items])
@endsection
