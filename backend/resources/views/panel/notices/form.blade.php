@extends('panel.layout')
@section('title', $notice ? 'ویرایش پیام' : 'پیام جدید')

@section('content')
<div class="head">
    <h1>{{ $notice ? 'ویرایش پیام' : 'پیام جدید' }}</h1>
    <a class="btn" href="{{ route('panel.notices.index') }}">بازگشت</a>
</div>

@if ($errors->any())
    <div class="alert err" role="alert">{{ $errors->first() }}</div>
@endif

<form class="card" method="POST" action="{{ $notice ? route('panel.notices.update', $notice->id) : route('panel.notices.store') }}" style="max-width:720px">
    @csrf
    @if ($notice) @method('PUT') @endif

    <div class="field">
        <label for="title">عنوان</label>
        <input id="title" type="text" name="title" maxlength="160" required value="{{ old('title', $notice->title ?? '') }}">
    </div>
    <div class="field">
        <label for="message">متن پیام</label>
        <textarea id="message" name="message" maxlength="5000" required>{{ old('message', $notice->message ?? '') }}</textarea>
    </div>
    <div class="field">
        <label class="check">
            <input type="checkbox" name="published" value="1" @checked(old('published', $notice->published ?? false))>
            منتشر شود (در صندوق پیام برنامه نمایش داده می‌شود)
        </label>
    </div>
    <button class="btn primary" type="submit">ذخیره</button>
</form>
@endsection
