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

<style>
    .sort-btn { all: unset; cursor: pointer; font: inherit; color: inherit; display: inline-flex; gap: 4px; align-items: center; border-radius: 6px; padding: 0 2px; }
    .sort-btn:focus-visible { outline: 2px solid var(--primary); outline-offset: 2px; }
    .sort-btn::after { content: '↕'; opacity: .4; font-size: 12px; }
    th[aria-sort="ascending"] .sort-btn::after { content: '▲'; opacity: 1; }
    th[aria-sort="descending"] .sort-btn::after { content: '▼'; opacity: 1; }
</style>
<div class="card table-wrap">
    <table id="lectures-table">
        <thead><tr>
            <th aria-sort="ascending" data-sort="pos"><button type="button" class="sort-btn">#</button></th>
            <th>ترتیب</th>
            <th aria-sort="none" data-sort="title"><button type="button" class="sort-btn">عنوان</button></th>
            <th>صوت</th>
            <th aria-sort="none" data-sort="status"><button type="button" class="sort-btn">وضعیت</button></th>
            <th></th>
        </tr></thead>
        <tbody>
        @forelse ($items as $item)
            <tr data-pos="{{ $loop->iteration }}" data-title="{{ $item->title }}" data-status="{{ $item->published ? 1 : 0 }}">
                <td style="white-space:nowrap">{{ $loop->iteration }}</td>
                <td style="white-space:nowrap">
                    <form method="POST" action="{{ route('panel.lectures.move', [$scholar->id, $item->id, 'up']) }}" style="display:inline">
                        @csrf @method('PATCH')
                        <button class="btn sm" type="submit" data-move @disabled($loop->first) aria-label="بالاتر">▲</button>
                    </form>
                    <form method="POST" action="{{ route('panel.lectures.move', [$scholar->id, $item->id, 'down']) }}" style="display:inline">
                        @csrf @method('PATCH')
                        <button class="btn sm" type="submit" data-move @disabled($loop->last) aria-label="پایین‌تر">▼</button>
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
                    <div style="margin-top:4px">@include('panel.lectures._ai_badge', ['lecture' => $item])</div>
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
            <tr><td colspan="6" class="empty">هنوز سخنرانی‌ای اضافه نشده است؛ در برنامه «به‌زودی» نمایش داده می‌شود.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<script>
    // Client-side sorting. "#" is each lecture's position in the app's order and stays with its row;
    // the ▲/▼ reorder buttons act on that real order, so they are disabled while another sort is shown.
    (function () {
        var table = document.getElementById('lectures-table');
        var body = table.tBodies[0];
        var rows = Array.prototype.slice.call(body.querySelectorAll('tr[data-pos]'));
        if (rows.length < 2) return;
        var heads = table.querySelectorAll('th[data-sort]');
        var collator = new Intl.Collator('fa', { numeric: true, sensitivity: 'base' });
        var keys = {
            pos: function (r) { return Number(r.dataset.pos); },
            title: function (r) { return r.dataset.title; },
            status: function (r) { return Number(r.dataset.status); }
        };
        function compare(a, b) {
            return typeof a === 'number' ? a - b : collator.compare(a, b);
        }
        function setMoveButtons(enabled) {
            table.querySelectorAll('button[data-move]').forEach(function (btn) {
                if (enabled) {
                    if (btn.dataset.wasDisabled !== '1') btn.disabled = false;
                } else {
                    if (btn.dataset.wasDisabled === undefined) btn.dataset.wasDisabled = btn.disabled ? '1' : '0';
                    btn.disabled = true;
                }
            });
        }
        heads.forEach(function (th) {
            th.querySelector('button').addEventListener('click', function () {
                var key = th.dataset.sort;
                var dir = th.getAttribute('aria-sort') === 'ascending' ? -1 : 1;
                heads.forEach(function (h) { h.setAttribute('aria-sort', 'none'); });
                th.setAttribute('aria-sort', dir === 1 ? 'ascending' : 'descending');
                var get = keys[key];
                rows.slice().sort(function (a, b) {
                    return dir * compare(get(a), get(b)) || Number(a.dataset.pos) - Number(b.dataset.pos);
                }).forEach(function (r) { body.appendChild(r); });
                setMoveButtons(key === 'pos' && dir === 1);
            });
        });
    })();
</script>
@endsection
