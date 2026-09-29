@extends('panel.layout')
@section('title', 'سخنرانی‌های ' . $scholar->name)

@section('content')
<div class="head">
    <h1>سخنرانی‌های {{ $scholar->name }}</h1>
    <div class="actions">
        <a class="btn" href="{{ route('panel.scholars.index') }}">بازگشت به علما</a>
        <a class="btn primary" href="{{ route('panel.lectures.create', $scholar->id) }}">سخنرانی جدید</a>
    </div>
</div>
@unless ($scholar->published)
    <div class="alert err" role="status">این استاد پنهان است؛ سخنرانی‌هایش تا نمایش دادن استاد در برنامه دیده نمی‌شوند.</div>
@endunless

<div class="card table-wrap">
    <table>
        <thead><tr><th>ترتیب</th><th>عنوان</th><th>صوت</th><th>وضعیت</th><th></th></tr></thead>
        <tbody>
        @forelse ($items as $item)
            <tr>
                <td style="white-space:nowrap">
                    <form method="POST" action="{{ route('panel.lectures.move', [$scholar->id, $item->id, 'up']) }}" style="display:inline">
                        @csrf @method('PATCH')
                        <button class="btn sm" type="submit" @disabled($loop->first) aria-label="بالاتر">▲</button>
                    </form>
                    <form method="POST" action="{{ route('panel.lectures.move', [$scholar->id, $item->id, 'down']) }}" style="display:inline">
                        @csrf @method('PATCH')
                        <button class="btn sm" type="submit" @disabled($loop->last) aria-label="پایین‌تر">▼</button>
                    </form>
                </td>
                <td>
                    <strong>{{ $item->title }}</strong>
                    @if ($item->description)
                        <div class="muted msg">{{ \Illuminate\Support\Str::limit($item->description, 140) }}</div>
                    @endif
                </td>
                <td>
                    @if ($item->resolved_url)
                        <audio controls preload="none" src="{{ $item->resolved_url }}" style="max-width:260px"></audio>
                        <div class="muted msg">{{ $item->audio_path ? 'فایل بارگذاری‌شده' : 'لینک خارجی' }}@if ($item->duration_sec) · {{ intdiv($item->duration_sec, 60) }}:{{ str_pad($item->duration_sec % 60, 2, '0', STR_PAD_LEFT) }}@endif</div>
                    @endif
                </td>
                <td>
                    @if ($item->published)
                        <span class="badge ok">نمایش در برنامه</span>
                    @else
                        <span class="badge draft">پنهان</span>
                    @endif
                </td>
                <td>
                    <div class="actions">
                        <a class="btn sm" href="{{ route('panel.lectures.edit', [$scholar->id, $item->id]) }}">ویرایش</a>
                        <form method="POST" action="{{ route('panel.lectures.toggle', [$scholar->id, $item->id]) }}">
                            @csrf @method('PATCH')
                            <button class="btn sm" type="submit">{{ $item->published ? 'پنهان کردن' : 'نمایش' }}</button>
                        </form>
                        <form method="POST" action="{{ route('panel.lectures.destroy', [$scholar->id, $item->id]) }}" onsubmit="return confirm('این سخنرانی (و فایل صوتی‌اش) حذف شود؟')">
                            @csrf @method('DELETE')
                            <button class="btn sm danger" type="submit">حذف</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="empty">هنوز سخنرانی‌ای اضافه نشده است؛ در برنامه «به‌زودی» نمایش داده می‌شود.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
