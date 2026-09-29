@php
    use App\Support\PanelFormat as F;
    $total = array_sum($items) ?: 1;
    $top = $items ? max($items) : 1;
@endphp
<div class="card">
    <h2 class="card-title">{{ $title }}</h2>
    @if (!$items)
        <div class="empty sm">داده‌ای برای این بازه ثبت نشده است.</div>
    @else
        <ul class="bars">
            @foreach ($items as $key => $count)
                <li>
                    <span class="bar" style="--w: {{ round($count * 100 / $top, 1) }}%"></span>
                    <span class="bar-label" @if (!empty($ltr)) dir="ltr" @endif>{{ ($names ?? [])[$key] ?? $key }}</span>
                    <span class="bar-value">{{ F::number($count) }} <small>{{ F::digits(round($count * 100 / $total)) }}٪</small></span>
                </li>
            @endforeach
        </ul>
    @endif
</div>
