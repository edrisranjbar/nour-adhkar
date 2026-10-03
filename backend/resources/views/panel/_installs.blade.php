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
    .inst-chart { position: relative; height: 210px; margin-top: 8px; border-radius: 14px; background: var(--bg); overflow: hidden; touch-action: pan-y; }
    .inst-chart svg { width: 100%; height: 100%; display: block; }
    .inst-chart .grid { stroke: var(--line); stroke-width: 1; stroke-dasharray: 3 5; }
    .inst-chart .line { fill: none; stroke-width: 2.5; stroke-linejoin: round; stroke-linecap: round; }
    .inst-chart .dot { stroke: var(--surface); stroke-width: 2; }
    .inst-chart .guide { stroke: var(--muted); stroke-width: 1; opacity: .35; }
    .inst-chart .ylab, .inst-chart .xlab { font-size: 11px; fill: var(--muted); font-family: inherit; }
    .inst-tip { position: absolute; top: 8px; pointer-events: none; background: var(--surface); border: 1px solid var(--line); border-radius: 10px;
        padding: 6px 10px; font-size: 12px; line-height: 1.7; box-shadow: 0 6px 18px -10px rgba(0,0,0,.35); white-space: nowrap; direction: rtl; }
    .inst-tip[hidden] { display: none; }
    .inst-tip i { display: inline-block; width: 8px; height: 8px; border-radius: 50%; margin-inline-end: 6px; }
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
    <div class="inst-meta" style="margin-top:4px">مجموع نصب‌ها در هر روز · ۱۴ روز اخیر</div>
    <div class="inst-chart" id="inst-chart" dir="ltr">
        <svg id="inst-svg" role="img" aria-label="نمودار مجموع نصب‌ها در هر روز از ۱۴ روز اخیر"></svg>
        <div class="inst-tip" id="inst-tip" hidden></div>
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
    var chartBox = document.getElementById('inst-chart');
    var tip = document.getElementById('inst-tip');
    var lastDaily = null;
    var dayFormat = new Intl.DateTimeFormat('fa-IR-u-ca-persian', { day: 'numeric', month: 'short', timeZone: 'Asia/Tehran' });

    var previous = {};   // last count seen per store, to detect increases
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
            var note = store.stale ? 'آخرین مقدار معتبر' : '';
            box.appendChild(el('small', '', note));
            totals.appendChild(box);
        });
    }

    // Rounds the axis maximum up to 1, 2, 2.5 or 5 × 10ⁿ so gridlines land on readable numbers.
    function niceMax(value) {
        if (value <= 0) return 4;
        var magnitude = Math.pow(10, Math.floor(Math.log10(value)));
        var steps = [1, 2, 2.5, 5, 10];
        for (var i = 0; i < steps.length; i++) if (steps[i] * magnitude >= value) return steps[i] * magnitude;
        return 10 * magnitude;
    }

    // Monotone cubic (Fritsch–Carlson) path: smooth, never overshoots, never dips below zero.
    function smoothPath(pts) {
        var n = pts.length;
        if (n === 1) return 'M' + pts[0][0] + ' ' + pts[0][1];
        var dx = [], slope = [], tangent = [];
        for (var i = 0; i < n - 1; i++) {
            dx[i] = pts[i + 1][0] - pts[i][0];
            slope[i] = (pts[i + 1][1] - pts[i][1]) / dx[i];
        }
        tangent[0] = slope[0];
        tangent[n - 1] = slope[n - 2];
        for (i = 1; i < n - 1; i++) {
            tangent[i] = slope[i - 1] * slope[i] <= 0 ? 0 : (slope[i - 1] + slope[i]) / 2;
        }
        for (i = 0; i < n - 1; i++) {
            if (slope[i] === 0) { tangent[i] = tangent[i + 1] = 0; continue; }
            var a = tangent[i] / slope[i], b = tangent[i + 1] / slope[i], h = a * a + b * b;
            if (h > 9) { var t = 3 / Math.sqrt(h); tangent[i] = t * a * slope[i]; tangent[i + 1] = t * b * slope[i]; }
        }
        var d = 'M' + pts[0][0].toFixed(1) + ' ' + pts[0][1].toFixed(1);
        for (i = 0; i < n - 1; i++) {
            var third = dx[i] / 3;
            d += ' C' + (pts[i][0] + third).toFixed(1) + ' ' + (pts[i][1] + tangent[i] * third).toFixed(1) +
                ' ' + (pts[i + 1][0] - third).toFixed(1) + ' ' + (pts[i + 1][1] - tangent[i + 1] * third).toFixed(1) +
                ' ' + pts[i + 1][0].toFixed(1) + ' ' + pts[i + 1][1].toFixed(1);
        }
        return d;
    }

    function dayLabel(day) { return dayFormat.format(new Date(day + 'T12:00:00Z')); }

    // Daily new installs per store as smooth lines over the last 14 Tehran days.
    function renderChart(daily) {
        lastDaily = daily;
        svg.textContent = '';
        tip.hidden = true;
        var days = (daily && daily.days) || [];
        var stores = (daily && daily.stores) || {};
        var keys = Object.keys(stores);
        var values = [];
        keys.forEach(function (key) { stores[key].forEach(function (v) { if (v !== null) values.push(v); }); });
        emptyEl.hidden = values.length > 0;
        if (!values.length || !days.length) return;

        var W = chartBox.clientWidth || 600, H = chartBox.clientHeight || 210;
        var L = 40, R = 16, T = 18, B = 28; // room for value labels (left) and day labels (bottom)
        svg.setAttribute('viewBox', '0 0 ' + W + ' ' + H);
        var top = niceMax(Math.max.apply(null, values));
        var x = function (i) { return days.length === 1 ? (L + W - R) / 2 : L + i * (W - L - R) / (days.length - 1); };
        var y = function (v) { return T + (1 - v / top) * (H - T - B); };
        var ns = 'http://www.w3.org/2000/svg';
        function add(tag, attrs, text) {
            var node = document.createElementNS(ns, tag);
            Object.keys(attrs).forEach(function (k) { node.setAttribute(k, attrs[k]); });
            if (text !== undefined) node.textContent = text;
            svg.appendChild(node);
            return node;
        }

        [0, 0.5, 1].forEach(function (f) {
            var v = top * f, yy = y(v);
            add('line', { 'class': 'grid', x1: L, x2: W - R, y1: yy, y2: yy });
            add('text', { 'class': 'ylab', x: L - 8, y: yy + 4, 'text-anchor': 'end' }, nf.format(Math.round(v)));
        });
        var labelEvery = W < 520 ? 3 : 2;
        days.forEach(function (day, i) {
            if ((days.length - 1 - i) % labelEvery !== 0) return;
            add('text', { 'class': 'xlab', x: x(i), y: H - 8, 'text-anchor': 'middle' }, i === days.length - 1 ? 'امروز' : dayLabel(day));
        });

        keys.forEach(function (key, index) {
            var color = COLORS[index % COLORS.length];
            // Days without enough history (null) split the line instead of being drawn as zero.
            var segments = [], current = [];
            stores[key].forEach(function (v, i) {
                if (v === null) { if (current.length) segments.push(current); current = []; }
                else current.push([x(i), y(v)]);
            });
            if (current.length) segments.push(current);
            segments.forEach(function (pts) {
                var line = smoothPath(pts);
                if (index === 0 && pts.length > 1) {
                    var gradientId = 'inst-fill-' + key;
                    var defs = add('defs', {});
                    var gradient = document.createElementNS(ns, 'linearGradient');
                    gradient.setAttribute('id', gradientId);
                    gradient.setAttribute('x1', 0); gradient.setAttribute('x2', 0); gradient.setAttribute('y1', 0); gradient.setAttribute('y2', 1);
                    [[0, 0.22], [1, 0]].forEach(function (stop) {
                        var s = document.createElementNS(ns, 'stop');
                        s.setAttribute('offset', stop[0]); s.setAttribute('stop-color', color); s.setAttribute('stop-opacity', stop[1]);
                        gradient.appendChild(s);
                    });
                    defs.appendChild(gradient);
                    add('path', { d: line + ' L' + pts[pts.length - 1][0].toFixed(1) + ' ' + y(0) + ' L' + pts[0][0].toFixed(1) + ' ' + y(0) + ' Z', fill: 'url(#' + gradientId + ')' });
                }
                add('path', { 'class': 'line', d: line, stroke: color });
            });
            // Only today's point gets a marker; hovering shows the rest.
            var lastIndex = stores[key].length - 1, last = stores[key][lastIndex];
            if (last !== null) add('circle', { 'class': 'dot', cx: x(lastIndex), cy: y(last), r: 4.5, fill: color });
        });

        svg.setAttribute('aria-label', 'نمودار مجموع نصب‌ها در هر روز از ۱۴ روز اخیر؛ امروز: ' + keys.map(function (key) {
            var v = stores[key][stores[key].length - 1];
            return (window.__instLabels && window.__instLabels[key] || key) + ' ' + (v === null ? '—' : nf.format(v));
        }).join('، '));

        chartBox.onpointermove = function (event) {
            var rect = chartBox.getBoundingClientRect();
            var px = (event.clientX - rect.left) * (W / rect.width);
            var i = Math.round((px - L) / ((W - L - R) / Math.max(1, days.length - 1)));
            i = Math.max(0, Math.min(days.length - 1, i));
            var old = svg.querySelector('.guide');
            if (old) old.remove();
            var guide = document.createElementNS(ns, 'line');
            guide.setAttribute('class', 'guide');
            guide.setAttribute('x1', x(i)); guide.setAttribute('x2', x(i)); guide.setAttribute('y1', T); guide.setAttribute('y2', H - B);
            svg.insertBefore(guide, svg.firstChild);
            tip.textContent = '';
            var title = document.createElement('div');
            title.style.fontWeight = '700';
            title.textContent = i === days.length - 1 ? 'امروز' : dayLabel(days[i]);
            tip.appendChild(title);
            keys.forEach(function (key, index) {
                var row = document.createElement('div');
                var dot = document.createElement('i');
                dot.style.background = COLORS[index % COLORS.length];
                row.appendChild(dot);
                var v = stores[key][i];
                row.appendChild(document.createTextNode((window.__instLabels && window.__instLabels[key] || key) + ': ' + (v === null ? '—' : nf.format(v) + ' نصب')));
                tip.appendChild(row);
            });
            tip.hidden = false;
            var left = (x(i) / W) * rect.width;
            tip.style.left = Math.max(4, Math.min(rect.width - tip.offsetWidth - 4, left - tip.offsetWidth / 2)) + 'px';
        };
        chartBox.onpointerleave = function () {
            tip.hidden = true;
            var old = svg.querySelector('.guide');
            if (old) old.remove();
        };
    }

    window.addEventListener('resize', function () { if (lastDaily) renderChart(lastDaily); });

    function handle(data) {
        var bazaarReviews = document.getElementById('bazaar-reviews');
        if (bazaarReviews && typeof data.bazaar_reviews_html === 'string') bazaarReviews.innerHTML = data.bazaar_reviews_html;
        var feedbackCount = document.getElementById('feedback-count');
        if (feedbackCount && data.feedback !== undefined) feedbackCount.textContent = nf.format(data.feedback);
        var bazaar = data.stores.bazaar;
        var votesEl = document.getElementById('bazaar-votes');
        var ratingEl = document.getElementById('bazaar-rating');
        var ratingStatus = document.getElementById('bazaar-rating-status');
        var stars = document.getElementById('bazaar-stars');
        if (votesEl && ratingEl && ratingStatus) {
            votesEl.textContent = !bazaar || bazaar.rating_count === null ? '—' : nf.format(bazaar.rating_count);
            var available = bazaar && bazaar.rating !== null;
            ratingEl.textContent = available ? nf.format(bazaar.rating) : '—';
            if (stars) stars.style.setProperty('--rating-fill', available ? Math.max(0, Math.min(100, bazaar.rating * 20)) + '%' : '0%');
            var description = available ? 'امتیاز بازار: ' + nf.format(bazaar.rating) + ' از ۵' : 'آمار بازار در دسترس نیست';
            if (available && bazaar.rating_stale) description += '؛ آخرین آمار معتبر بازار';
            ratingStatus.classList.toggle('stale', !!(bazaar && bazaar.rating_stale));
            ratingStatus.setAttribute('aria-label', description);
            ratingStatus.title = description + (bazaar && bazaar.rating_updated_at ? '؛ ' + new Date(bazaar.rating_updated_at * 1000).toLocaleString('fa-IR') : '');
        }
        var increased = [];
        Object.keys(data.stores).forEach(function (key) {
            var value = data.stores[key].installs;
            if (value === null) return;
            if (previous[key] !== undefined && value > previous[key]) increased.push(key);
            previous[key] = value;
        });
        lastDaily = data.daily || null;
        renderTotals(data.stores, increased);
        window.__instLabels = {};
        Object.keys(data.stores).forEach(function (key) { window.__instLabels[key] = data.stores[key].label; });
        renderChart(data.daily || null);
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
                if (ratingStatus) {
                    ratingStatus.classList.add('stale');
                    var rating = document.getElementById('bazaar-rating');
                    ratingStatus.setAttribute('aria-label', (rating && rating.textContent !== '—' ? 'امتیاز بازار: ' + rating.textContent + ' از ۵؛ ' : '') + 'به‌روزرسانی ناموفق بود');
                    ratingStatus.title = ratingStatus.getAttribute('aria-label');
                }
            });
    }

    paintSound();
    poll();
    setInterval(poll, Number(root.dataset.interval) * 1000);
})();
</script>
