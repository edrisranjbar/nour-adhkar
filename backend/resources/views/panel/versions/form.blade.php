@extends('panel.layout')
@section('title', $version ? 'ویرایش نسخه' : 'نسخه جدید')

@section('content')
<div class="head">
    <h1>{{ $version ? 'ویرایش نسخه' : 'نسخه جدید' }}</h1>
    <a class="btn" href="{{ route('panel.versions.index') }}">بازگشت</a>
</div>

@if ($errors->any())
    <div class="alert err" role="alert">{{ $errors->first() }}</div>
@endif

<form class="card" method="POST" action="{{ $version ? route('panel.versions.update', $version->id) : route('panel.versions.store') }}" style="max-width:720px">
    @csrf
    @if ($version) @method('PUT') @endif

    <div class="field-row">
        <div class="field">
            <label for="version_name">نام نسخه</label>
            <input id="version_name" type="text" name="version_name" maxlength="40" required dir="ltr" placeholder="2.3.0" value="{{ old('version_name', $version->version_name ?? '') }}">
        </div>
        <div class="field">
            <label for="version_code">کد نسخه (versionCode)</label>
            <input id="version_code" type="number" name="version_code" min="1" required dir="ltr" placeholder="21" value="{{ old('version_code', $version->version_code ?? '') }}">
        </div>
        <div class="field">
            <label for="release_date">تاریخ انتشار</label>
            <input id="release_date" type="date" name="release_date" required dir="ltr" value="{{ old('release_date', $version->release_date ?? now('Asia/Tehran')->toDateString()) }}">
        </div>
    </div>
    <div class="field">
        <label for="changelog">تغییرات</label>
        <textarea id="changelog" name="changelog" maxlength="5000" required placeholder="هر تغییر در یک خط">{{ old('changelog', $version->changelog ?? '') }}</textarea>
    </div>
    <div class="field">
        <label class="check">
            <input type="checkbox" name="is_required" value="1" @checked(old('is_required', $version->is_required ?? false))>
            به‌روزرسانی اجباری
        </label>
        <div class="muted">کاربرانی که نسخه‌ای پایین‌تر از این نسخه دارند، تا به‌روزرسانی نکنند نمی‌توانند از برنامه استفاده کنند. فقط برای مشکلات جدی استفاده شود.</div>
    </div>
    <div class="field">
        <label class="check">
            <input type="checkbox" name="published" value="1" @checked(old('published', $version->published ?? false))>
            منتشر شود (پس از تأیید در کافه‌بازار؛ برنامه این نسخه را به کاربران پیشنهاد می‌دهد)
        </label>
    </div>
    <button class="btn primary" type="submit">ذخیره</button>
</form>
@endsection
