@switch($lecture->ai_status)
    @case('queued')
    @case('processing')
        <span class="badge draft">در حال ساخت توضیح</span>
        @break
    @case('failed')
        <span class="badge off">ساخت توضیح ناموفق</span>
        @break
@endswitch
