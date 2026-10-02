@extends('panel.layout')
@section('title', 'نسخه‌های برنامه')
@php use App\Support\PanelFormat as F; @endphp

@section('content')
<div class="head">
    <h1>نسخه‌های برنامه</h1>
    <a class="btn primary" href="{{ route('panel.versions.create') }}">نسخه جدید</a>
</div>

<div class="grid">
    <div class="card stat">
        <div class="n">{{ $latest ? F::digits($latest) : '—' }}</div>
        <div class="l">آخرین کد نسخه منتشرشده</div>
    </div>
    <div class="card stat">
        <div class="n">{{ $minRequired ? F::digits($minRequired) : '—' }}</div>
        <div class="l">حداقل نسخه لازم (کاربران پایین‌تر باید به‌روزرسانی کنند)</div>
    </div>
</div>

<div class="card table-wrap">
    <table>
        <thead><tr><th>نسخه</th><th>تغییرات</th><th>تاریخ انتشار</th><th>نوع</th><th>وضعیت</th><th></th></tr></thead>
        <tbody>
        @forelse ($items as $item)
            <tr>
                <td style="white-space:nowrap">
                    <strong>{{ F::digits($item->version_name) }}</strong>
                    <div class="muted">کد {{ F::digits($item->version_code) }}</div>
                </td>
                <td><div class="muted msg">{{ \Illuminate\Support\Str::limit($item->changelog, 160) }}</div></td>
                <td class="muted" style="white-space:nowrap">{{ F::calendarDate($item->release_date) }}</td>
                <td>
                    @if ($item->is_required)
                        <span class="badge off">اجباری</span>
                    @else
                        <span class="badge">اختیاری</span>
                    @endif
                </td>
                <td>
                    @if ($item->published)
                        <span class="badge ok">منتشرشده</span>
                    @else
                        <span class="badge draft">پیش‌نویس</span>
                    @endif
                </td>
                <td>
                    <div class="actions">
                        <a class="btn sm" href="{{ route('panel.versions.edit', $item->id) }}">ویرایش</a>
                        <form method="POST" action="{{ route('panel.versions.toggle', $item->id) }}">
                            @csrf @method('PATCH')
                            <button class="btn sm" type="submit">{{ $item->published ? 'لغو انتشار' : 'انتشار' }}</button>
                        </form>
                        <form method="POST" action="{{ route('panel.versions.destroy', $item->id) }}" onsubmit="return confirm('این نسخه حذف شود؟')">
                            @csrf @method('DELETE')
                            <button class="btn sm danger" type="submit">حذف</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="empty">هنوز نسخه‌ای ثبت نشده است.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@include('panel.pager', ['items' => $items])
@endsection
