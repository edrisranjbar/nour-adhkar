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
                <button class="btn sm" type="submit" form="generate-form" >ساخت توضیح از روی صوت</button>
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
    (function () {
        var form = document.getElementById('generate-form');
        var box = document.getElementById('ai-generate');
        var button = box.querySelector('button');
        var status = document.getElementById('ai-generate-status');
        var field = document.getElementById('description');
        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            if (button.disabled) return;
            if (field.value.trim() && !confirm('توضیح فعلی با متن تولیدشده جایگزین شود؟')) return;
            var original = field.value;
            var controller = new AbortController();
            var timer = setTimeout(function () { controller.abort(); }, 60000);
            button.disabled = true;
            button.textContent = 'در حال ساخت توضیح…';
            box.setAttribute('aria-busy', 'true');
            status.textContent = 'در حال ارسال صوت و ساخت خلاصه؛ لطفاً صبر کنید…';
            try {
                var response = await fetch(form.action, {method:'POST', body:new FormData(form),
                    headers:{Accept:'application/json'}, credentials:'same-origin', signal:controller.signal});
                var data;
                try { data = await response.json(); } catch (_) { throw new Error('پاسخ نامعتبر از سرور؛ اتصال یا مهلت درخواست را بررسی کنید.'); }
                if (!response.ok) throw new Error(data.message || 'ساخت توضیح ناموفق بود.');
                if (field.value === original) {
                    field.value = data.description;
                    status.textContent = 'توضیح ساخته و ذخیره شد.';
                } else {
                    status.textContent = 'نتیجه در سرور ذخیره شد؛ ویرایش فعلی شما در این کادر حفظ شده است.';
                }
            } catch (error) {
                status.textContent = error.name === 'AbortError'
                    ? 'پاسخ در مهلت یک دقیقه دریافت نشد؛ وضعیت سرور را بررسی و سپس دوباره تلاش کنید.'
                    : error.message;
            } finally {
                clearTimeout(timer);
                button.disabled = false;
                button.textContent = 'ساخت توضیح از روی صوت';
                box.setAttribute('aria-busy', 'false');
            }
        });
    })();
    </script>
@endif
@endsection