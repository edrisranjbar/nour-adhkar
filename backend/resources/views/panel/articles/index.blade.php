@extends('panel.layout')
@section('title', 'مقالات')
@php use App\Support\PanelFormat as F; @endphp

@section('content')
<div class="head">
    <h1>مقالات برنامه</h1>
    <a class="btn primary" href="{{ route('panel.articles.create') }}">مقاله جدید</a>
</div>

<div class="card table-wrap">
    <table>
        <thead><tr><th>عنوان</th><th>وضعیت</th><th>تاریخ انتشار</th><th>بازدید</th><th></th></tr></thead>
        <tbody>
        @forelse ($items as $item)
            <tr>
                <td>
                    <strong>{{ $item->title }}</strong>
                    <div class="muted msg">{{ \Illuminate\Support\Str::limit($item->excerpt ?: strip_tags($item->content), 140) }}</div>
                </td>
                <td>
                    @if ($item->status === 'published')
                        <span class="badge ok">منتشرشده</span>
                    @else
                        <span class="badge draft">پیش‌نویس</span>
                    @endif
                </td>
                <td class="muted" style="white-space:nowrap">{{ $item->published_at ? F::date($item->published_at) : '—' }}</td>
                <td>{{ F::number($item->views ?? 0) }}</td>
                <td>
                    <div class="actions">
                        <a class="btn sm" href="{{ route('panel.articles.edit', $item->id) }}">ویرایش</a>
                        <form method="POST" action="{{ route('panel.articles.toggle', $item->id) }}">
                            @csrf @method('PATCH')
                            <button class="btn sm" type="submit">{{ $item->status === 'published' ? 'لغو انتشار' : 'انتشار' }}</button>
                        </form>
                        <form method="POST" action="{{ route('panel.articles.destroy', $item->id) }}" onsubmit="return confirm('این مقاله حذف شود؟')">
                            @csrf @method('DELETE')
                            <button class="btn sm danger" type="submit">حذف</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="empty">هنوز مقاله‌ای نوشته نشده است.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@include('panel.pager', ['items' => $items])
@endsection
