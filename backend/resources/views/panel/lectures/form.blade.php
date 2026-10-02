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
        <textarea id="description" name="description" maxlength="5000">{{ old('description', $lecture->description ?? '') }}</textarea>
    </div>

    <fieldset class="field" style="border:1px solid var(--border, #e5e0d5);border-radius:12px;padding:12px 14px">
        <legend>فایل صوتی</legend>
        @if ($lecture && ($lecture->audio_path || $lecture->audio_url))
            <p class="muted">صوت فعلی: {{ $lecture->audio_path ? 'فایل بارگذاری‌شده' : 'لینک خارجی' }}. برای جایگزینی، فایل تازه بارگذاری کنید یا لینک تازه وارد کنید؛ در غیر این صورت همان صوت می‌ماند.</p>
        @endif
        <label for="audio_file">بارگذاری فایل (mp3، m4a، aac، ogg، wav؛ حداکثر ۱۰۰ مگابایت)</label>
        <input id="audio_file" type="file" name="audio_file" accept=".mp3,.m4a,.aac,.ogg,.oga,.wav,audio/*">
        <div class="muted" style="margin:10px 0">یا</div>
        <label for="audio_url">لینک مستقیم صوت (https)</label>
        <input id="audio_url" type="url" name="audio_url" maxlength="500" dir="ltr" placeholder="https://example.com/lecture.mp3" value="{{ old('audio_url', $lecture->audio_url ?? '') }}">
    </fieldset>

    <div class="field">
        <label>مدت (اختیاری؛ برنامه مدت واقعی را خودش هم تشخیص می‌دهد)</label>
        @php($dur = $lecture->duration_sec ?? null)
        <div style="display:flex;gap:8px;align-items:center;max-width:280px">
            <input type="number" name="duration_min" min="0" max="1440" placeholder="دقیقه" value="{{ old('duration_min', $dur ? intdiv($dur, 60) : '') }}" aria-label="دقیقه">
            <span>:</span>
            <input type="number" name="duration_sec_part" min="0" max="59" placeholder="ثانیه" value="{{ old('duration_sec_part', $dur ? $dur % 60 : '') }}" aria-label="ثانیه">
        </div>
    </div>
    @if ($lecture)
        <fieldset class="field" style="border:1px solid var(--line);border-radius:12px;padding:12px 14px">
            <legend>خلاصه و متن سخنرانی (خودکار)</legend>
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:10px">
                @include('panel.lectures._ai_badge', ['lecture' => $lecture])
                @if ($lecture->ai_status === 'failed' && $lecture->ai_error)
                    <span class="muted" dir="auto">{{ $lecture->ai_error }}</span>
                @endif
            </div>
            @if ($lecture->ai_status === 'done' || $lecture->transcript)
                <div class="field">
                    <label for="summary">خلاصه</label>
                    <textarea id="summary" name="summary" maxlength="20000" style="min-height:160px">{{ old('summary', $lecture->summary) }}</textarea>
                </div>
                <div class="field">
                    <label for="transcript">متن کامل سخنرانی</label>
                    <textarea id="transcript" name="transcript" style="min-height:320px">{{ old('transcript', $lecture->transcript) }}</textarea>
                </div>
                <label class="check">
                    <input type="checkbox" name="text_published" value="1" @checked(old('text_published', $lecture->text_published))>
                    خلاصه و متن را بررسی کرده‌ام؛ در برنامه نمایش داده شوند
                </label>
            @else
                <p class="muted">پردازش خودکار هر ۳۰ دقیقه اجرا می‌شود؛ پس از آن، خلاصه و متن این‌جا برای بررسی نمایش داده می‌شوند.</p>
            @endif
        </fieldset>
    @endif

    <div class="field">
        <label class="check">
            <input type="checkbox" name="published" value="1" @checked(old('published', $lecture->published ?? true))>
            در برنامه نمایش داده شود
        </label>
    </div>
    <button class="btn primary" type="submit">ذخیره</button>
</form>

@if ($lecture && in_array($lecture->ai_status, ['done', 'failed'], true))
    <form method="POST" action="{{ route('panel.lectures.reprocess', [$scholar->id, $lecture->id]) }}" style="margin-top:12px"
          onsubmit="return confirm('خلاصه و متن دوباره ساخته شوند؟ ویرایش‌های فعلی جایگزین می‌شوند و تا بررسی دوباره در برنامه نمایش داده نمی‌شوند.')">
        @csrf @method('PATCH')
        <button class="btn sm" type="submit">ساخت دوباره خلاصه و متن</button>
    </form>
@endif
@endsection
