@extends('panel.layout')
@section('title', 'هوش مصنوعی سخنرانی‌ها')
@section('content')
<div class="head"><h1>هوش مصنوعی سخنرانی‌ها</h1></div>
<section class="card" style="max-width:760px">
    <p class="muted">ابتدا آدرس و کلید را ذخیره کنید، سپس فهرست مدل‌ها را دریافت و دو مدل را انتخاب کنید. کلید ذخیره‌شده نمایش داده نمی‌شود.</p>
    <form method="post" action="{{ route('panel.lecture-ai.save') }}">
        @csrf @method('PUT')
        <div class="field"><label for="base_url">آدرس پایهٔ API</label><input id="base_url" name="base_url" type="url" dir="ltr" required value="{{ $settings->base_url ?? 'https://ai.manageit.dev/api/v1' }}" style="width:100%"></div>
        <div class="field"><label for="api_key">کلید محرمانهٔ API</label><input id="api_key" name="api_key" type="password" dir="ltr" autocomplete="new-password" placeholder="{{ $settings?->api_key ? 'کلید ثبت شده؛ خالی بگذارید تا حفظ شود' : 'کلید سرویس' }}"></div>
        @foreach (['transcription_model' => 'مدل تبدیل صوت به متن', 'summary_model' => 'مدل خلاصه‌سازی'] as $field => $label)
            <div class="field"><label for="{{ $field }}">{{ $label }}</label><select id="{{ $field }}" name="{{ $field }}"><option value="">انتخاب نشده</option>
                @foreach ($models as $model)
                    <option value="{{ $model['id'] }}" @selected(($settings?->{$field} ?? '') === $model['id'])>{{ $model['id'] }} — {{ $model['free'] ? 'رایگان' : 'غیررایگان یا قیمت نامشخص' }}</option>
                @endforeach
            </select></div>
        @endforeach
        <p class="muted">همهٔ مدل‌های دریافت‌شده، از جمله مدل‌های پولی و با قیمت نامشخص، قابل انتخاب‌اند. مدل صوت باید مسیر audio/transcriptions سرویس را پشتیبانی کند؛ مدل متنی برای خلاصه‌سازی است. رایگان بودن به معنی نداشتن محدودیت مصرف نیست.</p>
        <button class="btn primary">ذخیره تنظیمات</button>
    </form>
    <form method="post" action="{{ route('panel.lecture-ai.models') }}" style="margin-top:16px">@csrf<button class="btn">دریافت / تازه‌سازی مدل‌ها</button></form>
</section>
@endsection
