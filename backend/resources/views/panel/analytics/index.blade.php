@extends('panel.layout')
@section('title', 'آمار')
@php
    use App\Support\PanelFormat as F;
    use App\Support\Analytics\Report;
    use App\Http\Controllers\Panel\FeedbackController;

    $range = $report->days;
    $dayLabel = fn ($row) => isset($row['hour']) ? 'ساعت '.F::digits(sprintf('%02d', $row['hour'])).':۰۰' : F::day($row['date']);

    $eventNames = [
        'store_click' => 'کلیک دریافت از کافه‌بازار', 'video_play' => 'پخش ویدیوی معرفی', 'video_unmute' => 'روشن‌کردن صدای ویدیو',
        'section_view' => 'دیدن بخش‌های صفحه', 'lang_switch' => 'تغییر زبان', 'theme_toggle' => 'تغییر حالت روشن و تاریک',
        'donate_click' => 'کلیک حمایت مالی', 'github_click' => 'کلیک GitHub',
    ];
    $eventLabels = [
        'hero' => 'بالای صفحه', 'footer' => 'انتهای صفحه', 'features' => 'امکانات', 'privacy' => 'حریم خصوصی', 'download' => 'دریافت',
        'fa' => 'فارسی', 'ar' => 'عربی', 'light' => 'روشن', 'dark' => 'تاریک',
    ];
    $names = [
        'devices' => ['mobile' => 'موبایل', 'desktop' => 'دسکتاپ', 'tablet' => 'تبلت', 'unknown' => 'نامشخص'],
        'referrers' => ['direct' => 'مستقیم / نامشخص'],
        'langs' => ['fa' => 'فارسی', 'ar' => 'عربی', 'en' => 'انگلیسی', 'tr' => 'ترکی', 'ur' => 'اردو', 'unknown' => 'نامشخص'],
        'countries' => ['IR' => 'ایران', 'AF' => 'افغانستان', 'IQ' => 'عراق', 'TJ' => 'تاجیکستان', 'DE' => 'آلمان', 'US' => 'آمریکا', 'GB' => 'انگلستان', 'TR' => 'ترکیه', 'AE' => 'امارات', 'CA' => 'کانادا', 'SE' => 'سوئد', 'NL' => 'هلند', 'SA' => 'عربستان', 'unknown' => 'نامشخص'],
        'os' => ['unknown' => 'نامشخص'],
        'browsers' => ['unknown' => 'نامشخص'],
    ];
    $weekdays = [6 => 'شنبه', 0 => 'یکشنبه', 1 => 'دوشنبه', 2 => 'سه‌شنبه', 3 => 'چهارشنبه', 4 => 'پنجشنبه', 5 => 'جمعه'];
@endphp

@push('head')
<style>
    .an-head { align-items: flex-end; }
    .an-head p { margin: 2px 0 0; color: var(--muted); font-size: 13px; }
    .an-controls { display: flex; gap: 10px; flex-wrap: wrap; }
    .seg { display: inline-flex; padding: 3px; gap: 2px; border: 1px solid var(--line); border-radius: 11px; background: var(--surface); }
    .seg a { padding: 4px 14px; border-radius: 8px; color: var(--muted); font-size: 14px; font-weight: 600; white-space: nowrap; }
    .seg a:hover { color: var(--text); }
    .seg a.active { background: var(--primary); color: #fff; }
    .live { display: inline-flex; align-items: center; gap: 8px; font-size: 13px; color: var(--muted); }
    .live i { width: 8px; height: 8px; border-radius: 50%; background: var(--primary); box-shadow: 0 0 0 0 var(--primary); animation: live 2s infinite; }
    @keyframes live { 70% { box-shadow: 0 0 0 7px transparent; } 100% { box-shadow: 0 0 0 0 transparent; } }
    .kpis { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; margin-bottom: 14px; }
    .kpi { padding: 16px 18px; }
    .kpi-label { color: var(--muted); font-size: 13px; }
    .kpi-value { font-size: 28px; font-weight: 800; line-height: 1.5; }
    .kpi-value small { font-size: 16px; font-weight: 700; color: var(--muted); margin-inline-start: 2px; }
    .kpi-delta { font-size: 12px; font-weight: 700; color: var(--muted); }
    .kpi-delta.up { color: var(--primary); }
    .kpi-delta.down { color: var(--danger); }
    .kpi-delta span { font-weight: 400; color: var(--muted); margin-inline-start: 4px; }
    .row { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 14px; margin-bottom: 14px; }
    .row.wide { grid-template-columns: 2fr 1fr; }
    @media (max-width: 1000px) { .row.wide { grid-template-columns: 1fr; } }
    .card-title { font-size: 15px; margin: 0 0 12px; }
    .card-sub { color: var(--muted); font-size: 12px; font-weight: 400; margin-inline-start: 6px; }
    .empty.sm { padding: 18px 0; font-size: 13px; }

    /* chart */
    .chart-legend { display: flex; gap: 18px; flex-wrap: wrap; margin-bottom: 10px; font-size: 13px; color: var(--muted); }
    .legend { display: inline-flex; align-items: center; gap: 6px; }
    .legend b { color: var(--text); }
    .legend i, .tip i { width: 10px; height: 10px; border-radius: 3px; background: currentColor; display: inline-block; }
    .s1 { color: var(--primary); } .s2 { color: #b08a4f; } .s3 { color: #6f8fb8; }
    .chart-body { display: grid; grid-template-columns: auto 1fr; grid-template-rows: 220px auto; column-gap: 10px; }
    .chart-y { display: flex; flex-direction: column; justify-content: space-between; font-size: 11px; color: var(--muted); padding-block: 2px 0; text-align: end; min-width: 28px; }
    .chart-plot { position: relative; }
    .chart-plot svg { position: absolute; inset: 0; width: 100%; height: 100%; overflow: visible; }
    .chart .grid { stroke: var(--line); stroke-width: 1; vector-effect: non-scaling-stroke; stroke-dasharray: 3 4; }
    .chart .grid.base { stroke-dasharray: none; }
    .chart .stroke { fill: none; stroke: currentColor; stroke-width: 2.25; vector-effect: non-scaling-stroke; stroke-linejoin: round; stroke-linecap: round; }
    .chart .area { fill: currentColor; opacity: 0.12; stroke: none; }
    .chart-cols { position: absolute; inset: 0; display: flex; }
    .col { position: relative; flex: 1; outline: none; }
    .col::before { content: ""; position: absolute; inset-block: 0; inset-inline-start: 50%; width: 1px; background: var(--muted); opacity: 0; transition: opacity .15s; }
    .col .dot { position: absolute; inset-inline-start: 50%; width: 9px; height: 9px; margin: 0 -4.5px -4.5px; border-radius: 50%; background: currentColor; border: 2px solid var(--surface); opacity: 0; transition: opacity .15s; }
    .tip { position: absolute; top: -6px; inset-inline-start: calc(50% + 10px); z-index: 3; min-width: 150px; padding: 8px 12px; border-radius: 10px; background: var(--text); color: var(--bg); font-size: 12px; line-height: 1.9; display: none; pointer-events: none; box-shadow: 0 8px 24px rgba(0,0,0,.2); }
    .tip b { display: block; font-size: 13px; }
    .tip span { display: flex; align-items: center; gap: 6px; }
    .col:nth-last-child(-n+4) .tip { inset-inline-start: auto; inset-inline-end: calc(50% + 10px); }
    .col:hover::before, .col:focus::before, .col:hover .dot, .col:focus .dot { opacity: .9; }
    .col:hover .tip, .col:focus .tip { display: block; }
    .chart-x { grid-column: 2; display: flex; font-size: 11px; color: var(--muted); margin-top: 6px; }
    .chart-x span { flex: 1; text-align: center; white-space: nowrap; overflow: visible; width: 0; }
    .sr-only { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }

    /* bars */
    .bars { list-style: none; margin: 0; padding: 0; display: grid; gap: 4px; }
    .bars li { position: relative; display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 5px 10px; border-radius: 8px; font-size: 14px; isolation: isolate; }
    .bar { position: absolute; inset-block: 0; inset-inline-start: 0; width: var(--w); background: var(--primary-soft); border-radius: 8px; z-index: -1; transform-origin: right; animation: grow .6s cubic-bezier(.2,.7,.2,1) both; }
    @keyframes grow { from { transform: scaleX(0); } }
    .bar-label { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .bar-value { font-weight: 700; white-space: nowrap; }
    .bar-value small { font-weight: 400; color: var(--muted); margin-inline-start: 4px; }

    /* heatmap */
    .heat { display: grid; grid-template-columns: auto repeat(24, 1fr); gap: 3px; font-size: 11px; color: var(--muted); align-items: center; }
    .heat .c { aspect-ratio: 1; border-radius: 4px; background: color-mix(in srgb, var(--primary) calc(var(--v) * 100%), var(--bg)); min-width: 8px; }
    .heat .h { text-align: center; }
    .heat-scale { display: flex; align-items: center; gap: 6px; justify-content: flex-end; margin-top: 10px; font-size: 11px; color: var(--muted); }
    .heat-scale i { width: 14px; height: 10px; border-radius: 3px; }

    /* funnel */
    .funnel { display: grid; gap: 10px; }
    .step { display: grid; gap: 4px; }
    .step-top { display: flex; justify-content: space-between; font-size: 14px; }
    .step-top b { font-weight: 800; }
    .step-bar { height: 12px; border-radius: 6px; background: var(--bg); overflow: hidden; }
    .step-bar span { display: block; height: 100%; width: var(--w); background: linear-gradient(to left, var(--primary), color-mix(in srgb, var(--primary) 60%, #b08a4f)); border-radius: 6px; transform-origin: right; animation: grow .8s cubic-bezier(.2,.7,.2,1) both; }
    .step small { color: var(--muted); font-size: 12px; }

    .events td:last-child, .events th:last-child { text-align: end; }
    .sub-labels { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 4px; }
    .note { color: var(--muted); font-size: 12px; margin-top: 18px; line-height: 2; }
    @media (max-width: 600px) {
        .kpis { grid-template-columns: 1fr 1fr; gap: 10px; }
        .kpi { padding: 12px 14px; }
        .kpi-value { font-size: 22px; }
        .kpi-delta span { display: none; }
        .chart-x span:nth-child(even) { visibility: hidden; }
        .heat { gap: 2px; font-size: 10px; }
        .an-controls { width: 100%; }
    }
</style>
@endpush

@section('content')
<div class="head an-head">
    <div>
        <h1>آمار و گزارش‌ها</h1>
        <p>{{ F::day($report->start->toDateString()) }} تا {{ F::day($report->end->toDateString()) }} · به وقت تهران</p>
    </div>
    <div class="an-controls">
        <nav class="seg" aria-label="بخش">
            <a href="{{ route('panel.analytics', ['tab' => 'web', 'range' => $range]) }}" @class(['active' => $tab === 'web'])>وب‌سایت</a>
            <a href="{{ route('panel.analytics', ['tab' => 'app', 'range' => $range]) }}" @class(['active' => $tab === 'app'])>برنامه</a>
        </nav>
        <nav class="seg" aria-label="بازهٔ زمانی">
            @foreach (Report::RANGES as $days => $name)
                <a href="{{ route('panel.analytics', ['tab' => $tab, 'range' => $days]) }}" @class(['active' => $range === $days])>{{ $name }}</a>
            @endforeach
        </nav>
    </div>
</div>

@if ($tab === 'web')
    @php $k = $data['kpis']; @endphp
    <p class="live" style="margin:-6px 0 14px"><i></i>{{ F::number($data['live']) }} بازدیدکننده در ۳۰ دقیقهٔ اخیر</p>

    <div class="kpis">
        @include('panel.analytics._kpi', ['label' => 'بازدیدکنندگان', 'value' => $k['visitors']])
        @include('panel.analytics._kpi', ['label' => 'بازدید صفحه', 'value' => $k['visits']])
        @include('panel.analytics._kpi', ['label' => 'نشست‌ها', 'value' => $k['sessions']])
        @include('panel.analytics._kpi', ['label' => 'کلیک دریافت از کافه‌بازار', 'value' => $k['storeClicks']])
        @include('panel.analytics._kpi', ['label' => 'نرخ تبدیل به کافه‌بازار', 'value' => $k['conversion'], 'suffix' => '٪', 'hint' => 'بازدیدکنندگانی که روی دریافت زدند'])
        @include('panel.analytics._kpi', ['label' => 'پخش ویدیو', 'value' => $k['videoPlays']])
        @include('panel.analytics._kpi', ['label' => 'صفحه در هر نشست', 'value' => $k['pagesPerSession']])
        @include('panel.analytics._kpi', ['label' => 'نشست تک‌صفحه‌ای', 'value' => $k['bounce'], 'suffix' => '٪'])
    </div>

    <div class="row wide">
        <div class="card">
            <h2 class="card-title">روند بازدید <span class="card-sub">{{ $range === 1 ? 'ساعتی' : 'روزانه' }}</span></h2>
            @include('panel.analytics._chart', ['rows' => $data['daily'], 'series' => [['visits', 'بازدید صفحه', 's1'], ['visitors', 'بازدیدکننده', 's2']], 'label' => $dayLabel])
        </div>
        <div class="card">
            <h2 class="card-title">قیف تبدیل</h2>
            @php $base = max(1, $data['funnel'][0][1]); @endphp
            <div class="funnel">
                @foreach ($data['funnel'] as [$key, $count])
                    <div class="step">
                        <div class="step-top">
                            <span>{{ ['visitors' => 'بازدید از سایت', 'section_view:features' => 'رسیدن به بخش امکانات', 'video_play' => 'پخش ویدیو', 'store_click' => 'کلیک دریافت از کافه‌بازار'][$key] }}</span>
                            <b>{{ F::number($count) }}</b>
                        </div>
                        <div class="step-bar"><span style="--w: {{ min(100, round($count * 100 / $base, 1)) }}%; animation-delay: {{ $loop->index * 0.12 }}s"></span></div>
                        @unless ($loop->first)<small>{{ F::digits(round($count * 100 / $base)) }}٪ از بازدیدکنندگان</small>@endunless
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom:14px">
        <h2 class="card-title">زمان بازدید <span class="card-sub">روز هفته × ساعت</span></h2>
        @php $heatMax = max(1, max(array_map('max', $data['heat']))); @endphp
        <div class="heat">
            <span></span>
            @for ($h = 0; $h < 24; $h++)<span class="h">{{ $h % 3 === 0 ? F::digits($h) : '' }}</span>@endfor
            @foreach ($weekdays as $dow => $dayName)
                <span>{{ $dayName }}</span>
                @for ($h = 0; $h < 24; $h++)
                    @php $v = $data['heat'][$dow][$h]; @endphp
                    <span class="c" style="--v: {{ $v ? round(0.15 + 0.85 * $v / $heatMax, 3) : 0 }}" title="{{ $dayName }}، ساعت {{ F::digits($h) }}: {{ F::number($v) }} بازدید"></span>
                @endfor
            @endforeach
        </div>
        <div class="heat-scale">کمتر <i style="background: color-mix(in srgb, var(--primary) 15%, var(--bg))"></i><i style="background: color-mix(in srgb, var(--primary) 50%, var(--bg))"></i><i style="background: var(--primary)"></i> بیشتر</div>
    </div>

    <div class="row">
        @include('panel.analytics._bars', ['title' => 'صفحه‌ها', 'items' => $data['breakdowns']['pages'], 'ltr' => true])
        @include('panel.analytics._bars', ['title' => 'منبع ورود', 'items' => $data['breakdowns']['referrers'], 'names' => $names['referrers'], 'ltr' => true])
        @include('panel.analytics._bars', ['title' => 'کمپین‌ها (utm_campaign)', 'items' => $data['breakdowns']['campaigns'], 'ltr' => true])
    </div>
    <div class="row">
        @include('panel.analytics._bars', ['title' => 'دستگاه', 'items' => $data['breakdowns']['devices'], 'names' => $names['devices']])
        @include('panel.analytics._bars', ['title' => 'سیستم‌عامل', 'items' => $data['breakdowns']['os'], 'names' => $names['os']])
        @include('panel.analytics._bars', ['title' => 'مرورگر', 'items' => $data['breakdowns']['browsers'], 'names' => $names['browsers']])
    </div>
    <div class="row">
        @include('panel.analytics._bars', ['title' => 'زبان مرورگر', 'items' => $data['breakdowns']['langs'], 'names' => $names['langs']])
        @include('panel.analytics._bars', ['title' => 'کشور', 'items' => $data['breakdowns']['countries'], 'names' => $names['countries']])
        @include('panel.analytics._bars', ['title' => 'منبع کمپین (utm_source)', 'items' => $data['breakdowns']['sources'], 'ltr' => true])
    </div>

    <div class="row wide">
        <div class="card table-wrap events">
            <h2 class="card-title">رویدادهای صفحهٔ اصلی</h2>
            @if (!$data['events']['counts'])
                <div class="empty sm">هنوز رویدادی ثبت نشده است.</div>
            @else
                <table>
                    <thead><tr><th>رویداد</th><th>بازدیدکننده</th><th>دفعات</th></tr></thead>
                    <tbody>
                    @foreach ($data['events']['counts'] as $name => $count)
                        <tr>
                            <td>
                                {{ $eventNames[$name] ?? $name }}
                                @if (!empty($data['events']['labels'][$name]))
                                    <div class="sub-labels">
                                        @foreach ($data['events']['labels'][$name] as $lab => $c)
                                            <span class="badge">{{ $eventLabels[$lab] ?? $lab }} · {{ F::number($c) }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td>{{ F::number($data['events']['visitors'][$name] ?? 0) }}</td>
                            <td>{{ F::number($count) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>
        <div class="card table-wrap">
            <h2 class="card-title">آخرین بازدیدها</h2>
            @forelse ($data['recent'] as $visit)
                <div style="display:flex;justify-content:space-between;gap:10px;padding:7px 0;border-top:1px solid var(--line);font-size:13px">
                    <span><span dir="ltr">{{ $visit->path }}</span> <span class="muted">· {{ $names['devices'][$visit->device ?? 'unknown'] ?? $visit->device }}@if ($visit->referrer_host) · <span dir="ltr">{{ $visit->referrer_host }}</span>@endif</span></span>
                    <span class="muted" style="white-space:nowrap">{{ F::date($visit->visited_at) }}</span>
                </div>
            @empty
                <div class="empty sm">هنوز بازدیدی ثبت نشده است.</div>
            @endforelse
        </div>
    </div>

    <p class="note">
        «بازدیدکننده» با شناسهٔ ناشناسی شمرده می‌شود که هر روز عوض می‌شود؛ نشانی IP ذخیره نمی‌شود و هیچ‌کس بین روزها دنبال نمی‌شود. بنابراین در بازه‌های چندروزه، بازدیدکنندهٔ تکراری در روزهای مختلف جداگانه شمرده می‌شود. ربات‌ها و خزنده‌ها ثبت نمی‌شوند. برای سنجش تبلیغات، به نشانی سایت پارامترهای utm_source و utm_campaign اضافه کنید (مثلاً ‎?utm_source=telegram&utm_campaign=launch‎).
    </p>
@else
    @php $k = $data['kpis']; @endphp
    <div class="kpis">
        @include('panel.analytics._kpi', ['label' => 'کاربران ثبت‌نام‌شده', 'value' => $k['users']])
        @include('panel.analytics._kpi', ['label' => 'ثبت‌نام جدید', 'value' => $k['newUsers']])
        @include('panel.analytics._kpi', ['label' => 'ایمیل تأییدشده', 'value' => $k['verified']])
        @include('panel.analytics._kpi', ['label' => 'ورود در این بازه', 'value' => $k['logins']])
        @include('panel.analytics._kpi', ['label' => 'نصب‌های دارای صندوق پیام', 'value' => $k['installations'], 'hint' => 'نصب‌هایی که دست‌کم یک پیام را خوانده‌اند'])
        @include('panel.analytics._kpi', ['label' => 'نصب فعال در این بازه', 'value' => $k['activeInstallations']])
        @include('panel.analytics._kpi', ['label' => 'بازخورد', 'value' => $k['feedback']])
    </div>

    <div class="row wide">
        <div class="card">
            <h2 class="card-title">ثبت‌نام و بازخورد <span class="card-sub">روزانه</span></h2>
            @include('panel.analytics._chart', ['rows' => $data['daily'], 'series' => [['signups', 'ثبت‌نام', 's1'], ['feedback', 'بازخورد', 's2']], 'label' => $dayLabel])
        </div>
        @include('panel.analytics._bars', ['title' => 'نوع بازخورد', 'items' => $data['feedbackTypes'], 'names' => FeedbackController::TYPES])
    </div>

    <div class="card table-wrap">
        <h2 class="card-title">خوانده‌شدن پیام‌ها</h2>
        @php $inst = max(1, $k['installations'][0]); @endphp
        <table>
            <thead><tr><th>پیام</th><th>انتشار</th><th>خوانده‌شده</th><th>پوشش</th></tr></thead>
            <tbody>
            @forelse ($data['notices'] as $notice)
                <tr>
                    <td>{{ $notice->title }}</td>
                    <td class="muted" style="white-space:nowrap">{{ F::date($notice->created_at) }}</td>
                    <td>{{ F::number($notice->reads) }}</td>
                    <td style="min-width:140px">
                        <div class="step-bar"><span style="--w: {{ min(100, round($notice->reads * 100 / $inst, 1)) }}%"></span></div>
                        <small class="muted">{{ F::digits(round($notice->reads * 100 / $inst)) }}٪ از نصب‌ها</small>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="empty">پیام منتشرشده‌ای وجود ندارد.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <p class="note">
        برنامه برای حفظ حریم خصوصی آمار استفاده نمی‌فرستد؛ این بخش فقط از داده‌هایی ساخته می‌شود که سرور خودش دارد: حساب‌های کاربری، بازخوردها و خوانده‌شدن پیام‌های صندوق. آمار نصب و به‌روزرسانی را در پنل توسعه‌دهندهٔ کافه‌بازار ببینید.
    </p>
@endif
@endsection
