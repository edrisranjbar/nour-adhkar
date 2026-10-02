@switch($lecture->ai_status)
    @case('pending')
        <span class="badge">متن: در صف پردازش</span>
        @break
    @case('processing')
        <span class="badge draft">متن: در حال پردازش</span>
        @break
    @case('failed')
        <span class="badge off">متن: ناموفق</span>
        @break
    @default
        @if ($lecture->text_published)
            <span class="badge ok">متن: در برنامه</span>
        @else
            <span class="badge draft">متن: آماده بررسی</span>
        @endif
@endswitch
