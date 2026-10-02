@extends('panel.layout')
@section('title', $article ? 'ویرایش مقاله' : 'مقاله جدید')

@section('content')
<div class="head">
    <h1>{{ $article ? 'ویرایش مقاله' : 'مقاله جدید' }}</h1>
    <a class="btn" href="{{ route('panel.articles.index') }}">بازگشت</a>
</div>

@if ($errors->any())
    <div class="alert err" role="alert">{{ $errors->first() }}</div>
@endif

<form class="card" method="POST" action="{{ $article ? route('panel.articles.update', $article->id) : route('panel.articles.store') }}" style="max-width:820px">
    @csrf
    @if ($article) @method('PUT') @endif

    <div class="field">
        <label for="title">عنوان</label>
        <input id="title" type="text" name="title" maxlength="200" required value="{{ old('title', $article->title ?? '') }}">
    </div>
    @unless ($article)
        <div class="field">
            <label for="slug">نشانی کوتاه لاتین (اختیاری)</label>
            <input id="slug" type="text" name="slug" maxlength="80" dir="ltr" placeholder="virtue-of-dhikr" value="{{ old('slug') }}">
        </div>
    @endunless
    <div class="field">
        <label for="excerpt">خلاصه (اختیاری؛ در فهرست مقالات نمایش داده می‌شود)</label>
        <textarea id="excerpt" name="excerpt" maxlength="500" style="min-height:90px">{{ old('excerpt', $article->excerpt ?? '') }}</textarea>
    </div>
    <div class="field">
        <label for="content">متن مقاله (هر بند را با یک خط خالی جدا کنید)</label>
        <textarea id="content" name="content" required style="min-height:420px;line-height:2">{{ old('content', $article->content ?? '') }}</textarea>
    </div>
    <div class="field">
        <label for="publish_at">زمان انتشار (اختیاری؛ زمان آینده یعنی انتشار زمان‌بندی‌شده)</label>
        <input id="publish_at" type="datetime-local" name="publish_at" dir="ltr" style="max-width:260px"
            value="{{ old('publish_at', !empty($article?->published_at) ? \Illuminate\Support\Carbon::parse($article->published_at, 'UTC')->setTimezone('Asia/Tehran')->format('Y-m-d\TH:i') : '') }}">
    </div>
    <div class="field">
        <label class="check">
            <input type="checkbox" name="published" value="1" @checked(old('published', ($article->status ?? '') === 'published'))>
            منتشر شود (در بخش مقالات برنامه نمایش داده می‌شود؛ اگر زمان انتشار در آینده باشد، از همان زمان)
        </label>
    </div>
    <button class="btn primary" type="submit">ذخیره</button>
</form>
@endsection
