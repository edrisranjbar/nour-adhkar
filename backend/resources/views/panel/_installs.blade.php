{{--
    Live store metrics. Reads the JSON feed at panel.installs every ten minutes, redraws the
    chart, and plays a short chime when a count goes up (after the sound button has been switched on).
--}}
@push('head')
<style>
    .installs .inst-top { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; margin-bottom: 8px; }
    .installs h2 { font-size: 17px; margin: 0; }
    .installs .inst-meta { color: var(--muted); font-size: 12px; }
    .inst-totals { display: flex; flex-wrap: wrap; gap: 10px 28px; margin: 6px 0 4px; }
    .inst-store { display: flex; flex-direction: column; min-width: 120px; }
    .inst-store .name { display: flex; align-items: center; gap: 6px; color: var(--muted); font-size: 13px; }
    .inst-store .name i { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }
    .inst-store b { font-size: 30px; line-height: 1.3; font-variant-numeric: tabular-nums; transition: color .3s; }
    .inst-store small { color: var(--muted); font-size: 12px; min-height: 18px; }
    .inst-store.up b { color: #2f9e6b; }
    .inst-store.stale b { opacity: .6; }
    .inst-chart { position: relative; height: 190px; margin-top: 6px; border-radius: 10px; background: var(--bg); overflow: hidden; }
    .inst-chart svg { width: 100%; height: 100%; display: block; }
    .inst-chart .grid { stroke: var(--line); stroke-width: 1; vector-effect: non-scaling-stroke; }
    .inst-chart .line { fill: none; stroke-width: 2.5; stroke-linejoin: round; stroke-linecap: round; vector-effect: non-scaling-stroke; }
    .inst-chart .area { stroke: none; opacity: .14; }
    .inst-chart .y { position: absolute; inset-inline-start: 8px; font-size: 11px; color: var(--muted); direction: ltr; }
    .inst-chart .y.max { top: 6px; }
    .inst-chart .y.min { bottom: 6px; }
    .inst-chart .empty { position: absolute; inset: 0; display: grid; place-items: center; padding: 0; }
    .inst-chart .empty[hidden], .inst-hint[hidden] { display: none; }
    .inst-hint { color: var(--muted); font-size: 12px; margin-top: 6px; }
</style>
@endpush

<div class="card installs" id="installs" data-url="{{ route('panel.installs') }}" data-interval="{{ config('stores.cache_seconds', 600) }}" style="margin-bottom:14px">
    <div class="inst-top">
        <h2>نصب‌ها <span class="inst-meta" id="inst-status">در حال دریافت…</span></h2>
        <button type="button" class="btn" id="inst-sound" aria-pressed="false">🔕 صدا: خاموش</button>
    </div>
    <div class="inst-totals" id="inst-totals" aria-live="polite"></div>
    <div class="inst-chart" dir="ltr">
        <svg id="inst-svg" viewBox="0 0 600 190" preserveAspectRatio="none" role="img" aria-label="نمودار تعداد نصب در ۷ روز اخیر"></svg>
        <span class="y max" id="inst-ymax"></span>
        <span class="y min" id="inst-ymin"></span>
        <div class="empty" id="inst-empty" hidden>داده‌ای برای نمودار نیست.</div>
    </div>
    <div class="inst-hint" id="inst-hint" hidden>برای شنیدن صدا، یک بار روی صفحه کلیک کنید (مرورگر پخش خودکار صدا را مسدود می‌کند).</div>
</div>

<script>
(function () {
    var root = document.getElementById('installs');
    if (!root) return;

    var url = root.dataset.url;
    var COLORS = ['#2f9e6b', '#e08a1e', '#3b82f6'];
    var nf = new Intl.NumberFormat('fa-IR');
    var totals = document.getElementById('inst-totals');
    var svg = document.getElementById('inst-svg');
    var statusEl = document.getElementById('inst-status');
    var soundBtn = document.getElementById('inst-sound');
    var hint = document.getElementById('inst-hint');
    var emptyEl = document.getElementById('inst-empty');
    var yMax = document.getElementById('inst-ymax');
    var yMin = document.getElementById('inst-ymin');
    var W = 600, H = 190, PAD = 14;

    var previous = {};   // last count seen per store, to detect increases
    var baseline = {};   // count when the page was opened, for the "+N since opening" note
    var audio = null;
    var soundOn = false;
    try { soundOn = localStorage.getItem('installSound') === '1'; } catch (e) {}

    function audioContext() {
        if (!audio) {
            var Ctx = window.AudioContext || window.webkitAudioContext;
            if (Ctx) audio = new Ctx();
        }
        return audio;
    }

    // A short two-note chime made with the Web Audio API (no sound file needed).
    function chime() {
        var a = audioContext();
        if (!a) return;
        if (a.state === 'suspended') a.resume();
        var start = a.currentTime;
        [[880, 0], [1318.5, 0.16]].forEach(function (note) {
            var osc = a.createOscillator(), gain = a.createGain(), t = start + note[1];
            osc.type = 'sine';
            osc.frequency.value = note[0];
            gain.gain.setValueAtTime(0.0001, t);
            gain.gain.exponentialRampToValueAtTime(0.3, t + 0.02);
            gain.gain.exponentialRampToValueAtTime(0.0001, t + 0.45);
            osc.connect(gain);
            gain.connect(a.destination);
            osc.start(t);
            osc.stop(t + 0.5);
        });
    }

    function paintSound() {
        soundBtn.textContent = soundOn ? '🔔 صدا: روشن' : '🔕 صدا: خاموش';
        soundBtn.setAttribute('aria-pressed', soundOn ? 'true' : 'false');
        hint.hidden = !(soundOn && !(audio && audio.state === 'running'));
    }

    // The click on the button is the user gesture browsers require before audio may play.
    soundBtn.addEventListener('click', function () {
        soundOn = !soundOn;
        try { localStorage.setItem('installSound', soundOn ? '1' : '0'); } catch (e) {}
        if (soundOn) chime();
        paintSound();
    });
    // After a reload with sound saved as on, the first click anywhere unlocks audio again.
    document.addEventListener('click', function () {
        if (soundOn) { var a = audioContext(); if (a && a.state === 'suspended') a.resume().then(paintSound); }
    });

    function el(tag, className, text) {
        var node = document.createElement(tag);
        if (className) node.className = className;
        if (text !== undefined) node.textContent = text;
        return node;
    }

    function renderTotals(stores, increasedKeys) {
        totals.textContent = '';
        Object.keys(stores).forEach(function (key, index) {
            var store = stores[key];
            var box = el('div', 'inst-store' + (store.stale ? ' stale' : '') + (increasedKeys.indexOf(key) >= 0 ? ' up' : ''));
            var name = el('span', 'name');
            var dot = el('i');
            dot.style.background = COLORS[index % COLORS.length];
            name.appendChild(dot);
            name.appendChild(document.createTextNode(store.label));
            box.appendChild(name);
            box.appendChild(el('b', '', store.installs === null ? '—' : nf.format(store.installs)));
            var gained = (store.installs !== null && baseline[key] !== undefined) ? store.installs - baseline[key] : 0;
            var note = gained > 0 ? '+' + nf.format(gained) + ' از زمان باز کردن صفحه' : (store.stale ? 'آخرین مقدار معتبر' : '');
            box.appendChild(el('small', '', note));
            totals.appendChild(box);
        });
    }

    function renderChart(series) {
        svg.textContent = '';
        var keys = Object.keys(series);
        var all = [];
        keys.forEach(function (key) { all = all.concat(series[key]); });
        emptyEl.hidden = all.length > 0;
        yMax.textContent = yMin.textContent = '';
        if (!all.length) return;

        var t0 = Math.min.apply(null, all.map(function (p) { return p[0]; }));
        var t1 = Math.max.apply(null, all.map(function (p) { return p[0]; }));
        var lo = Math.min.apply(null, all.map(function (p) { return p[1]; }));
        var hi = Math.max.apply(null, all.map(function (p) { return p[1]; }));
        if (hi === lo) { hi += 1; lo = Math.max(0, lo - 1); }
        var spread = hi - lo;
        lo = Math.max(0, lo - spread * 0.1);
        hi = hi + spread * 0.1;
        var x = function (t) { return t1 === t0 ? W / 2 : PAD + (t - t0) / (t1 - t0) * (W - 2 * PAD); };
        var y = function (v) { return H - PAD - (v - lo) / (hi - lo) * (H - 2 * PAD); };
        var ns = 'http://www.w3.org/2000/svg';

        [0.25, 0.5, 0.75].forEach(function (f) {
            var line = document.createElementNS(ns, 'line');
            line.setAttribute('class', 'grid');
            line.setAttribute('x1', 0); line.setAttribute('x2', W);
            line.setAttribute('y1', H * f); line.setAttribute('y2', H * f);
            svg.appendChild(line);
        });

        keys.forEach(function (key, index) {
            var points = series[key];
            if (!points.length) return;
            var color = COLORS[index % COLORS.length];
            // A single point is drawn as a short flat line so it is still visible.
            var coords = points.length === 1 ? [[PAD, points[0][1]], [W - PAD, points[0][1]]] : points.map(function (p) { return [p[0], p[1]]; });
            var px = coords.map(function (p) { return points.length === 1 ? p[0] : x(p[0]); });
            var d = coords.map(function (p, i) { return (i ? 'L' : 'M') + px[i].toFixed(1) + ' ' + y(p[1]).toFixed(1); }).join(' ');
            if (index === 0) {
                var area = document.createElementNS(ns, 'path');
                area.setAttribute('class', 'area');
                area.setAttribute('fill', color);
                area.setAttribute('d', d + ' L' + px[px.length - 1].toFixed(1) + ' ' + (H - PAD) + ' L' + px[0].toFixed(1) + ' ' + (H - PAD) + ' Z');
                svg.appendChild(area);
            }
            var path = document.createElementNS(ns, 'path');
            path.setAttribute('class', 'line');
            path.setAttribute('stroke', color);
            path.setAttribute('d', d);
            svg.appendChild(path);
        });

        yMax.textContent = nf.format(Math.round(hi));
        yMin.textContent = nf.format(Math.round(lo));
    }

    function handle(data) {
        var feedbackCount = document.getElementById('feedback-count');
        if (feedbackCount && data.feedback !== undefined) feedbackCount.textContent = nf.format(data.feedback);
        var bazaar = data.stores.bazaar;
        var votesEl = document.getElementById('bazaar-votes');
        var ratingEl = document.getElementById('bazaar-rating');
        var ratingStatus = document.getElementById('bazaar-rating-status');
        if (votesEl && ratingEl && ratingStatus) {
            votesEl.textContent = !bazaar || bazaar.rating_count === null ? '—' : nf.format(bazaar.rating_count);
            ratingEl.textContent = !bazaar || bazaar.rating === null ? '—' : nf.format(bazaar.rating) + ' از ۵';
            ratingStatus.textContent = !bazaar || bazaar.rating === null ? 'آمار بازار در دسترس نیست' : (bazaar.rating_stale ? 'آخرین آمار معتبر بازار' : '');
            ratingStatus.title = bazaar && bazaar.rating_updated_at ? new Date(bazaar.rating_updated_at * 1000).toLocaleString('fa-IR') : '';
        }
        var increased = [];
        Object.keys(data.stores).forEach(function (key) {
            var value = data.stores[key].installs;
            if (value === null) return;
            if (baseline[key] === undefined) baseline[key] = value;
            if (previous[key] !== undefined && value > previous[key]) increased.push(key);
            previous[key] = value;
        });
        renderTotals(data.stores, increased);
        renderChart(data.series || {});
        if (increased.length && soundOn) chime();
        statusEl.textContent = 'به‌روزرسانی: ' + new Date().toLocaleTimeString('fa-IR');
        paintSound();
    }

    function poll() {
        fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
            .then(function (response) { if (!response.ok) throw new Error(response.status); return response.json(); })
            .then(handle)
            .catch(function () {
                statusEl.textContent = 'دریافت ناموفق بود؛ ۱۰ دقیقهٔ دیگر دوباره تلاش می‌شود';
                var ratingStatus = document.getElementById('bazaar-rating-status');
                if (ratingStatus) ratingStatus.textContent = 'به‌روزرسانی آمار بازار ناموفق بود';
            });
    }

    paintSound();
    poll();
    setInterval(poll, Number(root.dataset.interval) * 1000);
})();
</script>
