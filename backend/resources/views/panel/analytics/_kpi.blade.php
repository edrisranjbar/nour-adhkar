@php
    use App\Support\PanelFormat as F;
    [$now, $before] = $value;
    $delta = F::delta($now, $before);
    $shown = is_float($now) ? F::decimal($now) : F::number($now);
@endphp
<div class="card kpi">
    <div class="kpi-label">{{ $label }}</div>
    <div class="kpi-value">{{ $shown }}@if (!empty($suffix))<small>{{ $suffix }}</small>@endif</div>
    @if ($delta !== null)
        <div @class(['kpi-delta', 'up' => $delta > 0, 'down' => $delta < 0]) title="در مقایسه با دورهٔ قبل">
            {{ $delta > 0 ? '▲' : ($delta < 0 ? '▼' : '•') }} {{ F::digits(abs($delta)) }}٪
            <span>نسبت به دورهٔ قبل</span>
        </div>
    @elseif ($before !== null)
        <div class="kpi-delta"><span>دورهٔ قبل: {{ F::number($before) }}</span></div>
    @elseif (!empty($hint))
        <div class="kpi-delta"><span>{{ $hint }}</span></div>
    @endif
</div>
