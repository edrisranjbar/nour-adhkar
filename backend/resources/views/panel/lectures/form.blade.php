@extends('panel.layout')
@section('title', $lecture ? 'ویرایش سخنرانی' : 'سخنرانی جدید')

@section('content')
<div class="head">
    <h1>{{ $lecture ? 'ویرایش سخنرانی' : 'سخنرانی جدید' }} · {{ $scholar->name }}</h1>
    <a class="btn" href="{{ route('panel.lectures.index', $scholar->id) }}">بازگشت</a>
</div>

@if ($errors->any())
    <div class="alert err" role="alert">{{ $errors->first() }}</div>
@endif

<form class="card" method="POST" enctype="multipart/form-data"
      action="{{ $lecture ? route('panel.lectures.update', [$scholar->id, $lecture->id]) : route('panel.lectures.store', $scholar->id) }}" style="max-width:720px">
    @csrf
    @if ($lecture) @method('PUT') @endif

    <div class="field">
        <label for="title">عنوان</label>
        <input id="title" type="text" name="title" maxlength="200" required value="{{ old('title', $lecture->title ?? '') }}">
    </div>
    <div class="field">
        <label for="description">توضیح (اختیاری)</label>
        <textarea id="description" name="description">{{ old('description', $lecture->description ?? '') }}</textarea>
        @if ($lecture && ($lecture->audio_path || $lecture->audio_url))
            @php($busy = in_array($lecture->ai_status, ['queued', 'processing'], true))
            <div id="ai-generate" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-top:8px"
                 data-status-url="{{ route('panel.lectures.ai-status', [$scholar->id, $lecture->id]) }}" data-busy="{{ $busy ? 1 : 0 }}">
                <button class="btn sm" type="button" id="ai-transcribe">تبدیل صوت به متن</button>
                <button class="btn sm" type="button" id="ai-summarize" @disabled(!empty($lecture->transcript))>خلاصه‌سازی متن</button>
                <span id="ai-progress" hidden aria-hidden="true">
                    <span style="display:inline-flex;align-items:center;gap:6px">
                        <span aria-hidden="true" style="width:12px;height:12px;border:2px solid currentColor;border-left-color:transparent;border-radius:50%;display:inline-block;animation:ai-spin .7s linear infinite"></span>
                        <span id="ai-progress-label"></span>
                    </span>
                </span>
                <span class="muted" id="ai-generate-status" role="status" aria-live="polite">
                    @if (false)
                        در حال تبدیل صوت به متن…
                    @elseif ($lecture->ai_status === 'failed')
                        ساخت توضیح ناموفق بود: <span dir="auto">{{ $lecture->ai_error }}</span>
                    @endif
                </span>
            </div>
        @endif
    </div>

    @include('panel.lectures._audio', ['audio' => $audio ?? null, 'lecture' => $lecture])

    <div class="field">
        <label class="check">
            <input type="checkbox" name="published" value="1" @checked(old('published', $lecture->published ?? true))>
            در برنامه نمایش داده شود
        </label>
    </div>
    <button class="btn primary" type="submit">ذخیره</button>
</form>

@if ($lecture && ($lecture->audio_path || $lecture->audio_url))
    <form id="generate-form" method="POST" action="{{ route('panel.lectures.generate', [$scholar->id, $lecture->id]) }}" hidden>@csrf</form>
    <script>
    var aiStyle = document.createElement('style'); aiStyle.textContent = '@keyframes ai-spin{to{transform:rotate(360deg)}}'; document.head.appendChild(aiStyle);
    (function () {
        var form = document.getElementById('generate-form');
        var box = document.getElementById('ai-generate');
        var transcribe = document.getElementById('ai-transcribe');
        var summarize = document.getElementById('ai-summarize');
        var progress = document.getElementById('ai-progress');
        var progressLabel = document.getElementById('ai-progress-label');
        var status = document.getElementById('ai-generate-status');
        var field = document.getElementById('description');
        async function runPhase(phase) {
            var button = phase === 'transcribe' ? transcribe : summarize;
            if (button.disabled) return;
            if (phase === 'summary' && field.value.trim() && !confirm('توضیح فعلی با خلاصه و متن جدید جایگزین شود؟')) return;
            var original = field.value;
            var controller = new AbortController();
            var timer = setTimeout(function () { controller.abort(); }, phase === 'transcribe' ? 210000 : 70000);
            transcribe.disabled = true; summarize.disabled = true;
            box.setAttribute('aria-busy', 'true');
            progress.hidden = false; progress.setAttribute('aria-hidden', 'false');
            progressLabel.textContent = phase === 'transcribe' ? 'در حال تبدیل صوت به متن…' : 'در حال خلاصه‌سازی…';
            status.textContent = phase === 'transcribe' ? 'صوت در حال پردازش است؛ این مرحله برای فایل‌های طولانی ممکن است چند دقیقه طول بکشد.' : 'متن دریافت شد؛ خلاصه‌سازی در حال انجام است…';
            try {
                var payload = new FormData(form); payload.set('phase', phase);
                var response = await fetch(form.action, {method:'POST', body:payload, headers:{Accept:'application/json'}, credentials:'same-origin', signal:controller.signal});
                var data; try { data = await response.json(); } catch (_) { throw new Error('پاسخ نامعتبر از سرور؛ اتصال یا مهلت درخواست را بررسی کنید.'); }
                if (!response.ok) throw new Error(data.message || 'پردازش ناموفق بود.');
                if (phase === 'transcribe') {
                    summarize.disabled = false; status.textContent = 'تبدیل صوت به متن انجام شد؛ اکنون می‌توانید خلاصه‌سازی را اجرا کنید.';
                } else if (field.value === original) { field.value = data.description; status.textContent = 'خلاصه و متن کامل ساخته و ذخیره شد.'; }
            } catch (error) {
                status.textContent = error.name === 'AbortError'
                    ? 'این مرحله بیش از زمان مجاز طول کشید؛ متن ناقص ذخیره نشد. دوباره تلاش کنید.'
                    : error.message;
            } finally {
                clearTimeout(timer);
                transcribe.disabled = false;
                if (phase === 'summary') summarize.disabled = false;
                progress.hidden = true; progress.setAttribute('aria-hidden', 'true');
                box.setAttribute('aria-busy', 'false');
            }
        }
        transcribe.addEventListener('click', function () { runPhase('transcribe'); });
        summarize.addEventListener('click', function () { runPhase('summary'); });
    })();
    </script>
@endif
@endsection
