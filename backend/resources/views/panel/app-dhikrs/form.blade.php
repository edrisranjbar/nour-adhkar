@extends('panel.layout')
@php use App\Support\AppDhikrCatalog as Catalog; @endphp
@section('title', $dhikr ? 'ویرایش ذکر' : 'ذکر جدید')

@section('content')
<div class="head">
    <h1>{{ $dhikr ? 'ویرایش ذکر' : 'ذکر جدید' }} · {{ Catalog::collectionTitle($collection) }}</h1>
    <a class="btn" href="{{ route('panel.app-dhikrs.index', $collection->id) }}">بازگشت</a>
</div>

@if ($errors->any())
    <div class="alert err" role="alert">{{ $errors->first() }}</div>
@endif

<form class="card" method="POST" action="{{ $dhikr ? route('panel.app-dhikrs.update', [$collection->id, $dhikr->id]) : route('panel.app-dhikrs.store', $collection->id) }}" style="max-width:720px">
    @csrf
    @if ($dhikr) @method('PUT') @endif

    <div class="field">
        <label for="arabic_text">متن عربی</label>
        <textarea id="arabic_text" name="arabic_text" required maxlength="10000" dir="rtl" lang="ar" style="min-height:140px;font-size:18px;line-height:1.9">{{ old('arabic_text', $dhikr->arabic_text ?? '') }}</textarea>
        <small class="muted">متن را با اعراب و دقیقاً مطابق منبع بنویسید؛ در برنامه همین متن نمایش داده می‌شود.</small>
    </div>
    <div class="field">
        <label for="translation">ترجمه فارسی</label>
        <textarea id="translation" name="translation" maxlength="10000" style="min-height:110px">{{ old('translation', $dhikr->translation ?? '') }}</textarea>
        <small class="muted">در زبان فارسی و دری نمایش داده می‌شود؛ در عربی، اردو و کوردی فقط متن عربی دیده می‌شود.</small>
    </div>
    <div class="field-row">
        <div class="field">
            <label for="source">منبع</label>
            <input id="source" type="text" name="source" maxlength="500" placeholder="مثلاً صحیح مسلم، حدیث ۲۷۲۳" value="{{ old('source', $dhikr->source ?? '') }}">
        </div>
        <div class="field">
            <label for="target_count">تعداد تکرار</label>
            <input id="target_count" type="number" name="target_count" min="1" max="10000" required value="{{ old('target_count', $dhikr->target_count ?? 1) }}">
        </div>
    </div>
    <div class="field">
        <label class="check">
            <input type="checkbox" name="published" value="1" @checked(old('published', $dhikr->published ?? true))>
            در برنامه نمایش داده شود
        </label>
    </div>
    <button class="btn primary" type="submit">ذخیره</button>
</form>
@endsection
