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
    <div class="field">
        <label class="check">
            <input type="checkbox" name="published" value="1" @checked(old('published', $lecture->published ?? true))>
            در برنامه نمایش داده شود
        </label>
    </div>
    <button class="btn primary" type="submit">ذخیره</button>
</form>
@endsection
