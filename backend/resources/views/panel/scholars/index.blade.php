@extends('panel.layout')
@section('title', 'علما و سخنرانی‌ها')
@php use App\Support\PanelFormat as F; @endphp

@section('content')
<div class="head">
    <h1>علما و سخنرانی‌ها</h1>
    <a class="btn primary" href="{{ route('panel.scholars.create') }}">استاد جدید</a>
</div>
<p class="muted">ترتیب این فهرست همان ترتیب نمایش در بخش «علما و مشاهیر» برنامه است. استادهای پنهان در برنامه دیده نمی‌شوند.</p>

<div class="card table-wrap">
    <table>
        <thead><tr><th>ترتیب</th><th>استاد</th><th>وضعیت</th><th>سخنرانی‌ها</th><th></th></tr></thead>
        <tbody>
        @forelse ($items as $item)
            <tr>
                <td style="white-space:nowrap">
                    <form method="POST" action="{{ route('panel.scholars.move', [$item->id, 'up']) }}" style="display:inline">
                        @csrf @method('PATCH')
                        <button class="btn sm" type="submit" @disabled($loop->first) aria-label="بالاتر">▲</button>
                    </form>
                    <form method="POST" action="{{ route('panel.scholars.move', [$item->id, 'down']) }}" style="display:inline">
                        @csrf @method('PATCH')
                        <button class="btn sm" type="submit" @disabled($loop->last) aria-label="پایین‌تر">▼</button>
                    </form>
                </td>
                <td>
                    @if ($item->photo_path)
                        <img src="{{ asset('storage/' . $item->photo_path) }}" alt="" style="width:36px;height:36px;border-radius:10px;object-fit:cover;vertical-align:middle">
                    @else
                        <span title="بدون عکس" aria-hidden="true" style="display:inline-block;width:36px;height:36px;border-radius:10px;vertical-align:middle;background:hsl({{ $item->hue }},45%,40%)"></span>
                    @endif
                    <strong>{{ $item->name }}</strong>
                    @unless ($item->photo_path)<span class="badge draft">بدون عکس</span>@endunless
                    <div class="muted msg">{{ $item->tagline }} <span dir="ltr">({{ $item->slug }})</span></div>
                </td>
                <td>
                    @if ($item->published)
                        <span class="badge ok">نمایش در برنامه</span>
                    @else
                        <span class="badge draft">پنهان</span>
                    @endif
                </td>
                <td><a href="{{ route('panel.lectures.index', $item->id) }}">{{ F::number($item->lectures_count) }} سخنرانی</a></td>
                <td>
                    <div class="actions">
                        <a class="btn sm primary" href="{{ route('panel.lectures.index', $item->id) }}">سخنرانی‌ها</a>
                        <a class="btn sm" href="{{ route('panel.scholars.edit', $item->id) }}">ویرایش</a>
                        <form method="POST" action="{{ route('panel.scholars.toggle', $item->id) }}">
                            @csrf @method('PATCH')
                            <button class="btn sm" type="submit">{{ $item->published ? 'پنهان کردن' : 'نمایش' }}</button>
                        </form>
                        <form method="POST" action="{{ route('panel.scholars.destroy', $item->id) }}" onsubmit="return confirm('این استاد و همه سخنرانی‌هایش (همراه با فایل‌های صوتی) حذف شوند؟')">
                            @csrf @method('DELETE')
                            <button class="btn sm danger" type="submit">حذف</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="empty">هنوز استادی اضافه نشده است.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
