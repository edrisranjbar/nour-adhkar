@extends('panel.layout')
@section('title', 'اذکار برنامه')
@php use App\Support\PanelFormat as F; use App\Support\AppDhikrCatalog as Catalog; @endphp

@section('content')
<div class="head">
    <h1>اذکار برنامه</h1>
    <a class="btn primary" href="{{ route('panel.app-adhkar.create') }}">مجموعه جدید</a>
</div>
<p class="muted">ذکرهای این بخش به اذکار داخلی برنامه اضافه می‌شوند: یا به انتهای یکی از مجموعه‌های برنامه (مثل اذکار صبحگاه)، یا به‌صورت مجموعه‌ای تازه در صفحه «اذکار و ادعیه». برنامه هر چند ساعت یک بار در پس‌زمینه فهرست را به‌روز می‌کند، پس تغییرات ممکن است کمی بعد در گوشی کاربران دیده شوند.</p>

<div class="card table-wrap">
    <table>
        <thead><tr><th>ترتیب</th><th>مجموعه</th><th>وضعیت</th><th>ذکرها</th><th></th></tr></thead>
        <tbody>
        @forelse ($items as $item)
            <tr>
                <td style="white-space:nowrap">
                    <form method="POST" action="{{ route('panel.app-adhkar.move', [$item->id, 'up']) }}" style="display:inline">
                        @csrf @method('PATCH')
                        <button class="btn sm" type="submit" @disabled($loop->first) aria-label="بالاتر">▲</button>
                    </form>
                    <form method="POST" action="{{ route('panel.app-adhkar.move', [$item->id, 'down']) }}" style="display:inline">
                        @csrf @method('PATCH')
                        <button class="btn sm" type="submit" @disabled($loop->last) aria-label="پایین‌تر">▼</button>
                    </form>
                </td>
                <td>
                    <strong>{{ Catalog::collectionTitle($item) }}</strong>
                    @if ($item->builtin_key)
                        <span class="badge">افزودن به مجموعه‌ی برنامه</span>
                    @else
                        <span class="badge">مجموعه‌ی جدید</span>
                        @if ($item->description)<div class="muted msg">{{ $item->description }}</div>@endif
                    @endif
                </td>
                <td>
                    @if ($item->published)
                        <span class="badge ok">نمایش در برنامه</span>
                    @else
                        <span class="badge draft">پنهان</span>
                    @endif
                </td>
                <td><a href="{{ route('panel.app-dhikrs.index', $item->id) }}">{{ F::number($item->dhikrs_count) }} ذکر</a></td>
                <td>
                    <div class="actions">
                        <a class="btn sm primary" href="{{ route('panel.app-dhikrs.index', $item->id) }}">ذکرها</a>
                        <a class="btn sm" href="{{ route('panel.app-adhkar.edit', $item->id) }}">ویرایش</a>
                        <form method="POST" action="{{ route('panel.app-adhkar.toggle', $item->id) }}">
                            @csrf @method('PATCH')
                            <button class="btn sm" type="submit">{{ $item->published ? 'پنهان کردن' : 'نمایش' }}</button>
                        </form>
                        <form method="POST" action="{{ route('panel.app-adhkar.destroy', $item->id) }}" onsubmit="return confirm('این مجموعه و همه ذکرهایش حذف شوند؟ ذکرهای داخلی برنامه تغییری نمی‌کنند.')">
                            @csrf @method('DELETE')
                            <button class="btn sm danger" type="submit">حذف</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="empty">هنوز مجموعه‌ای اضافه نشده است. برنامه فقط اذکار داخلی خودش را نشان می‌دهد.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
