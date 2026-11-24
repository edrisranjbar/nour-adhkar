@php use App\Support\PanelFormat as MyketFormat; @endphp
<div class="head" style="margin-bottom:6px">
    <h2 style="font-size:17px;margin:0">نظرات مایکت</h2>
    <a href="{{ route('panel.feedback.index', ['source' => 'myket']) }}">همهٔ نظرات دریافت‌شده</a>
</div>
<p class="muted" style="font-size:12px">
    {{ MyketFormat::number($myketStatus['count']) }} نظر دریافت‌شده از گزیدهٔ صفحهٔ عمومی · فقط نمایش
    @if ($myketStatus['stale'])
        · {{ $myketStatus['updated_at'] ? 'به‌روزرسانی ناموفق؛ آخرین نظرات ذخیره‌شده' : 'دریافت نظرات مایکت ناموفق بود؛ در به‌روزرسانی بعدی دوباره تلاش می‌شود' }}
    @endif
    @if ($myketStatus['updated_at'])
        · آخرین دریافت: {{ MyketFormat::date(\Carbon\Carbon::createFromTimestamp($myketStatus['updated_at'])) }}
    @endif
</p>
@forelse ($myketItems as $review)
    <article style="padding:12px 0;border-top:1px solid var(--line)">
        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
            <span class="badge">مایکت</span>
            <strong>{{ $review->author ?: 'کاربر مایکت' }}</strong>
            @if ($review->rating !== null)
                <span class="muted" aria-label="امتیاز {{ MyketFormat::digits($review->rating) }} از ۵">★ {{ MyketFormat::digits($review->rating) }} از ۵</span>
            @endif
            <span class="muted">{{ $review->date_label ?: 'تاریخ نامشخص' }}</span>
        </div>
        <div class="msg">{{ ($compact ?? false) ? \Illuminate\Support\Str::limit($review->message, 300) : $review->message }}</div>
    </article>
@empty
    <div class="empty">
        <svg aria-hidden="true" viewBox="0 0 24 24" width="32" height="32" style="fill:none;stroke:currentColor;stroke-width:1.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        <p>{{ $myketStatus['stale'] ? 'هنوز نظر ذخیره‌شده‌ای از مایکت نداریم.' : 'هنوز نظر متنی از مایکت دریافت نشده است.' }}</p>
    </div>
@endforelse
<a class="muted" href="{{ 'https://myket.ir/app/'.rawurlencode(config('stores.package')) }}" target="_blank" rel="noopener noreferrer">مشاهده در مایکت ↗</a>
