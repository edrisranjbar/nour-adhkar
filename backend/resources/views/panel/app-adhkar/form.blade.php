@extends('panel.layout')
@section('title', $collection ? 'ویرایش مجموعه' : 'مجموعه جدید')
@php use App\Support\AppDhikrCatalog as Catalog; @endphp

@section('content')
<div class="head">
    <h1>{{ $collection ? 'ویرایش مجموعه' : 'مجموعه جدید' }}</h1>
    <a class="btn" href="{{ route('panel.app-adhkar.index') }}">بازگشت</a>
</div>

@if ($errors->any())
    <div class="alert err" role="alert">{{ $errors->first() }}</div>
@endif

@php($kind = old('kind', $collection ? ($collection->builtin_key ? 'builtin' : 'new') : 'builtin'))
<form class="card" method="POST" action="{{ $collection ? route('panel.app-adhkar.update', $collection->id) : route('panel.app-adhkar.store') }}" style="max-width:720px">
    @csrf
    @if ($collection) @method('PUT') @endif

    <fieldset class="field" style="border:0;padding:0;margin:0 0 16px">
        <legend style="font-weight:600;margin-bottom:6px">نوع مجموعه</legend>
        <label class="check"><input type="radio" name="kind" value="builtin" @checked($kind === 'builtin')> افزودن ذکر به یکی از مجموعه‌های برنامه</label>
        <label class="check"><input type="radio" name="kind" value="new" @checked($kind === 'new')> مجموعه‌ی جدید در صفحه «اذکار و ادعیه»</label>
    </fieldset>

    <div class="field" data-kind="builtin">
        <label for="builtin_key">مجموعه‌ی برنامه</label>
        <select id="builtin_key" name="builtin_key">
            <option value="">انتخاب کنید</option>
            @foreach (Catalog::BUILT_IN as $key => $title)
                <option value="{{ $key }}" @selected(old('builtin_key', $collection->builtin_key ?? '') === $key)>{{ $title }}</option>
            @endforeach
        </select>
        <small class="muted">ذکرهای این مجموعه پس از ذکرهای داخلی همان مجموعه نمایش داده می‌شوند.</small>
    </div>

    <div data-kind="new">
        <div class="field">
            <label for="title">عنوان</label>
            <input id="title" type="text" name="title" maxlength="160" placeholder="مثلاً دعای باران" value="{{ old('title', $collection->title ?? '') }}">
        </div>
        <div class="field">
            <label for="description">توضیح کوتاه (اختیاری)</label>
            <input id="description" type="text" name="description" maxlength="300" value="{{ old('description', $collection->description ?? '') }}">
        </div>
        <div class="field">
            <label for="icon">نماد</label>
            <select id="icon" name="icon">
                @foreach (Catalog::ICONS as $key => $label)
                    <option value="{{ $key }}" @selected(old('icon', $collection->icon ?? 'menu_book') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="field">
        <label class="check">
            <input type="checkbox" name="published" value="1" @checked(old('published', $collection->published ?? true))>
            در برنامه نمایش داده شود
        </label>
    </div>
    <button class="btn primary" type="submit">ذخیره</button>
</form>
<script>
    // Show only the fields for the chosen kind of collection.
    (function () {
        var radios = document.querySelectorAll('input[name="kind"]');
        function sync() {
            var kind = document.querySelector('input[name="kind"]:checked').value;
            document.querySelectorAll('[data-kind]').forEach(function (el) { el.hidden = el.dataset.kind !== kind; });
        }
        radios.forEach(function (r) { r.addEventListener('change', sync); });
        sync();
    })();
</script>
@endsection
