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
                <button class="btn sm" type="submit" form="generate-form" @disabled($busy)>ساخت توضیح از روی صوت</button>
                <span class="muted" id="ai-generate-status" role="status" aria-live="polite">
                    @if ($busy)
                        در حال تبدیل صوت به متن…
                    @elseif ($lecture->ai_status === 'failed')
                        ساخت توضیح ناموفق بود: <span dir="auto">{{ $lecture->ai_error }}</span>
                    @endif
                </span>
            </div>
        @endif
    </div>

    <fieldset class="field" style="border:1px solid var(--border, #e5e0d5);border-radius:12px;padding:12px 14px">
        <legend>فایل صوتی</legend>
        <label for="audio_file">بارگذاری فایل (mp3، m4a، aac، ogg، wav؛ حداکثر ۱۰۰ مگابایت)</label>
        <input id="audio_file" type="file" name="audio_file" accept=".mp3,.m4a,.aac,.ogg,.oga,.wav,audio/*">
        <div class="muted" style="margin:10px 0">یا</div>
        <label for="audio_url">لینک مستقیم صوت (https)</label>
        <input id="audio_url" type="url" name="audio_url" maxlength="500" dir="ltr" placeholder="https://example.com/lecture.mp3" value="{{ old('audio_url', $lecture->audio_url ?? '') }}">
    </fieldset>

    <div class="field">
        <label class="check">
            <input type="checkbox" name="published" value="1" @checked(old('published', $lecture->published ?? true))>
            در برنامه نمایش داده شود
        </label>
    </div>
    <button class="btn primary" type="submit">ذخیره</button>
</form>

@if ($lecture && ($lecture->audio_path || $lecture->audio_url))
    <form id="generate-form" method="POST" action="{{ route('panel.lectures.generate', [$scholar->id, $lecture->id]) }}" hidden
          onsubmit="return document.getElementById('description').value.trim() === '' || confirm('توضیح فعلی با متن کامل سخنرانی جایگزین شود؟')">
        @csrf
    </form>
    <script>
        // While a description is being generated, check every 15 seconds and drop the result into the field.
        (function () {
            var box = document.getElementById('ai-generate');
            if (!box || box.dataset.busy !== '1') return;
            var status = document.getElementById('ai-generate-status');
            var button = box.querySelector('button');
            var timer = setInterval(async function () {
                try {
                    var response = await fetch(box.dataset.statusUrl, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
                    if (!response.ok) return;
                    var data = await response.json();
                    if (data.status === 'done') {
                        clearInterval(timer);
                        document.getElementById('description').value = data.description || '';
                        status.textContent = 'توضیح ساخته و ذخیره شد. در صورت نیاز ویرایش کنید.';
                        button.disabled = false;
                    } else if (data.status === 'failed') {
                        clearInterval(timer);
                        status.textContent = 'ساخت توضیح ناموفق بود: ' + (data.error || '');
                        button.disabled = false;
                    }
                } catch (e) { /* network blip; try again next tick */ }
            }, 15000);
        })();
    </script>
@endif
@endsection
