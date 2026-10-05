@if (!empty($item->user_id) && ($item->user_name || $item->user_email))
    <strong>{{ $item->user_name ?: '—' }}</strong>
    @if ($item->user_email)<div class="muted" dir="ltr" style="text-align:start">{{ $item->user_email }}</div>@endif
@elseif (!empty($item->user_id))
    <span class="muted">کاربر حذف‌شده</span>
@else
    <span class="muted">ناشناس</span>
@endif
