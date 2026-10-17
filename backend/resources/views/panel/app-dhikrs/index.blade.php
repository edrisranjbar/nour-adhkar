@extends('panel.layout')
@php use App\Support\AppDhikrCatalog as Catalog; use App\Support\PanelFormat as F; @endphp
@section('title', 'ذکرهای ' . Catalog::collectionTitle($collection))

@section('content')
<div class="head">
    <h1>ذکرهای {{ Catalog::collectionTitle($collection) }}</h1>
    <div class="actions">
        <a class="btn" href="{{ route('panel.app-adhkar.index') }}">بازگشت به مجموعه‌ها</a>
        <a class="btn primary" href="{{ route('panel.app-dhikrs.create', $collection->id) }}">ذکر جدید</a>
    </div>
</div>
@unless ($collection->published)
    <div class="alert err" role="status">این مجموعه پنهان است؛ ذکرهایش تا نمایش دادن مجموعه در برنامه دیده نمی‌شوند.</div>
@endunless
@if ($collection->builtin_key)
    <p class="muted">این ذکرها پس از ذکرهای داخلی «{{ Catalog::collectionTitle($collection) }}» در برنامه نمایش داده می‌شوند.</p>
@endif

<div class="card table-wrap">
    <table>
        <thead><tr><th>ترتیب</th><th>ذکر</th><th>تکرار</th><th>وضعیت</th><th></th></tr></thead>
        <tbody>
        @forelse ($items as $item)
            <tr>
                <td style="white-space:nowrap">
                    <form method="POST" action="{{ route('panel.app-dhikrs.move', [$collection->id, $item->id, 'up']) }}" style="display:inline">
                        @csrf @method('PATCH')
                        <button class="btn sm" type="submit" @disabled($loop->first) aria-label="بالاتر">▲</button>
                    </form>
                    <form method="POST" action="{{ route('panel.app-dhikrs.move', [$collection->id, $item->id, 'down']) }}" style="display:inline">
                        @csrf @method('PATCH')
                        <button class="btn sm" type="submit" @disabled($loop->last) aria-label="پایین‌تر">▼</button>
                    </form>
                </td>
                <td>
                    <div dir="rtl" lang="ar" style="font-size:17px;line-height:1.9">{{ \Illuminate\Support\Str::limit($item->arabic_text, 220) }}</div>
                    @if ($item->translation)<div class="muted msg">{{ \Illuminate\Support\Str::limit($item->translation, 160) }}</div>@endif
                    @if ($item->source)<div class="muted msg">منبع: {{ $item->source }}</div>@endif
                </td>
                <td>{{ F::number($item->target_count) }}</td>
                <td>
                    @if ($item->published)
                        <span class="badge ok">نمایش در برنامه</span>
                    @else
                        <span class="badge draft">پنهان</span>
                    @endif
                </td>
                <td>
                    <div class="actions">
                        <a class="btn sm" href="{{ route('panel.app-dhikrs.edit', [$collection->id, $item->id]) }}">ویرایش</a>
                        <form method="POST" action="{{ route('panel.app-dhikrs.toggle', [$collection->id, $item->id]) }}">
                            @csrf @method('PATCH')
                            <button class="btn sm" type="submit">{{ $item->published ? 'پنهان کردن' : 'نمایش' }}</button>
                        </form>
                        <form method="POST" action="{{ route('panel.app-dhikrs.destroy', [$collection->id, $item->id]) }}" onsubmit="return confirm('این ذکر حذف شود؟')">
                            @csrf @method('DELETE')
                            <button class="btn sm danger" type="submit">حذف</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="empty">هنوز ذکری اضافه نشده است؛ مجموعه‌ی خالی در برنامه نمایش داده نمی‌شود.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
