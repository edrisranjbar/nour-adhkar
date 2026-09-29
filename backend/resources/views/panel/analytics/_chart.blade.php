{{--
    Server-rendered time-series chart.
    $rows:   list of points (arrays); $series: [[key, label, cssClass], ...] (first series is drawn as a filled area)
    $label:  closure(row) => x-axis / tooltip label
--}}
@php
    use App\Support\Analytics\Chart;
    use App\Support\PanelFormat as F;
    $columns = array_map(fn ($s) => array_column($rows, $s[0]), $series);
    $max = Chart::max(...$columns);
    $n = count($rows);
    $every = max(1, (int) ceil($n / 6));
@endphp
<div class="chart">
    <div class="chart-legend">
        @foreach ($series as [$key, $name, $class])
            <span class="legend {{ $class }}"><i></i>{{ $name }} <b>{{ F::number(array_sum($columns[$loop->index])) }}</b></span>
        @endforeach
    </div>
    <div class="chart-body">
        <div class="chart-y" aria-hidden="true">
            @foreach ([4, 3, 2, 1, 0] as $step)
                <span>{{ F::number($max * $step / 4) }}</span>
            @endforeach
        </div>
        <div class="chart-plot">
            <svg viewBox="0 0 {{ Chart::W }} {{ Chart::H }}" preserveAspectRatio="none" aria-hidden="true">
                @foreach ([1, 2, 3] as $step)
                    <line class="grid" x1="0" x2="{{ Chart::W }}" y1="{{ Chart::y($max * $step / 4, $max) }}" y2="{{ Chart::y($max * $step / 4, $max) }}" />
                @endforeach
                <line class="grid base" x1="0" x2="{{ Chart::W }}" y1="{{ Chart::y(0, $max) }}" y2="{{ Chart::y(0, $max) }}" />
                @foreach ($series as [$key, $name, $class])
                    @if ($loop->first)
                        <path class="area {{ $class }}" d="{{ Chart::area($columns[0], $max) }}" />
                    @endif
                    <path class="stroke {{ $class }}" d="{{ Chart::line($columns[$loop->index], $max) }}" />
                @endforeach
            </svg>
            <div class="chart-cols">
                @foreach ($rows as $i => $row)
                    <div class="col" tabindex="0">
                        @foreach ($series as [$key, $name, $class])
                            <i class="dot {{ $class }}" style="bottom: {{ round((1 - Chart::y($row[$key], $max) / Chart::H) * 100, 2) }}%"></i>
                        @endforeach
                        <div class="tip">
                            <b>{{ $label($row) }}</b>
                            @foreach ($series as [$key, $name, $class])
                                <span class="{{ $class }}"><i></i>{{ $name }}: {{ F::number($row[$key]) }}</span>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="chart-x" aria-hidden="true">
            @foreach ($rows as $i => $row)
                <span>{{ ($i % $every === 0 || $i === $n - 1) ? $label($row) : '' }}</span>
            @endforeach
        </div>
    </div>
    <table class="sr-only">
        <thead><tr><th>زمان</th>@foreach ($series as [$key, $name]) <th>{{ $name }}</th> @endforeach</tr></thead>
        <tbody>
            @foreach ($rows as $row)
                <tr><td>{{ $label($row) }}</td>@foreach ($series as [$key]) <td>{{ $row[$key] }}</td> @endforeach</tr>
            @endforeach
        </tbody>
    </table>
</div>
