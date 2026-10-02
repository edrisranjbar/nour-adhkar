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
<div id="reorder-status" class="alert" role="status" aria-live="polite" hidden></div>
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
            <tr data-id="{{ $item->id }}" data-pos="{{ $loop->iteration }}" data-title="{{ $item->title }}" data-status="{{ $item->published ? 1 : 0 }}">
                <td data-position style="white-space:nowrap">{{ $loop->iteration }}</td>
                <td style="white-space:nowrap">
                    <form method="POST" action="{{ route('panel.lectures.move', [$scholar->id, $item->id, 'up']) }}" style="display:inline">
                        @csrf @method('PATCH')
                        <button class="btn sm" type="submit" data-move="up" @disabled($loop->first) aria-label="بالاتر">▲</button>
                    </form>
                    <form method="POST" action="{{ route('panel.lectures.move', [$scholar->id, $item->id, 'down']) }}" style="display:inline">
                        @csrf @method('PATCH')
                        <button class="btn sm" type="submit" data-move="down" @disabled($loop->last) aria-label="پایین‌تر">▼</button>
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
        var status = document.getElementById('reorder-status');
        var moving = false;
        var sortKey = 'pos';
        var sortDirection = 1;
        var collator = new Intl.Collator('fa', { numeric: true, sensitivity: 'base' });
        var keys = {
            pos: function (r) { return Number(r.dataset.pos); },
            title: function (r) { return r.dataset.title; },
            status: function (r) { return Number(r.dataset.status); }
        };
        function compare(a, b) {
            return typeof a === 'number' ? a - b : collator.compare(a, b);
        }
        function updateButtons() {
            rows.forEach(function (row) {
                var position = Number(row.dataset.pos);
                row.querySelectorAll('button[data-move]').forEach(function (btn) {
                    btn.disabled = moving || sortKey !== 'pos' || sortDirection !== 1
                        || (btn.dataset.move === 'up' ? position === 1 : position === rows.length);
                });
            });
            heads.forEach(function (head) {
                head.querySelector('button').disabled = moving;
            });
        }
        heads.forEach(function (th) {
            th.querySelector('button').addEventListener('click', function () {
                if (moving) return;
                var key = th.dataset.sort;
                var dir = th.getAttribute('aria-sort') === 'ascending' ? -1 : 1;
                sortKey = key;
                sortDirection = dir;
                heads.forEach(function (h) { h.setAttribute('aria-sort', 'none'); });
                th.setAttribute('aria-sort', dir === 1 ? 'ascending' : 'descending');
                var get = keys[key];
                rows.slice().sort(function (a, b) {
                    return dir * compare(get(a), get(b)) || Number(a.dataset.pos) - Number(b.dataset.pos);
                }).forEach(function (r) { body.appendChild(r); });
                updateButtons();
            });
        });
        rows.forEach(function (row) {
            row.querySelectorAll('button[data-move]').forEach(function (button) {
                button.form.addEventListener('submit', async function (event) {
                    event.preventDefault();
                    if (moving || button.disabled) return;
                    moving = true;
                    updateButtons();
                    table.setAttribute('aria-busy', 'true');
                    status.classList.remove('err');
                    status.textContent = 'در حال ذخیره ترتیب…';
                    status.hidden = false;
                    var controller = new AbortController();
                    var timeout = setTimeout(function () { controller.abort(); }, 30000);
                    try {
                        var response = await fetch(button.form.action, {
                            method: 'POST', body: new FormData(button.form), credentials: 'same-origin',
                            headers: { 'Accept': 'application/json' }, signal: controller.signal
                        });
                        if (!response.ok || response.redirected) throw new Error('request');
                        var data = await response.json();
                        var byId = new Map(rows.map(function (r) { return [r.dataset.id, r]; }));
                        if (!Array.isArray(data.order) || data.order.length !== rows.length
                            || new Set(data.order.map(String)).size !== rows.length
                            || !data.order.every(function (id) { return byId.has(String(id)); })) {
                            throw new Error('changed');
                        }
                        rows = data.order.map(function (id) { return byId.get(String(id)); });
                        rows.forEach(function (r, index) {
                            r.dataset.pos = String(index + 1);
                            r.querySelector('[data-position]').textContent = String(index + 1);
                            body.appendChild(r);
                        });
                        status.textContent = 'ترتیب ذخیره شد.';
                    } catch (error) {
                        status.classList.add('err');
                        status.textContent = error.message === 'changed'
                            ? 'فهرست سخنرانی‌ها تغییر کرده است؛ صفحه را تازه کنید تا ترتیب جدید نمایش داده شود.'
                            : 'ذخیره ترتیب تأیید نشد. اتصال و ورود به پنل را بررسی کنید و دوباره تلاش کنید.';
                    } finally {
                        clearTimeout(timeout);
                        moving = false;
                        table.setAttribute('aria-busy', 'false');
                        updateButtons();
                    }
                });
            });
        });
        updateButtons();
    })();
</script>
@endsection
