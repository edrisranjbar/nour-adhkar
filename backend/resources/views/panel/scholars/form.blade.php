@extends('panel.layout')
@section('title', $scholar ? 'ویرایش استاد' : 'استاد جدید')

@section('content')
<div class="head">
    <h1>{{ $scholar ? 'ویرایش استاد' : 'استاد جدید' }}</h1>
    <a class="btn" href="{{ route('panel.scholars.index') }}">بازگشت</a>
</div>

@if ($errors->any())
    <div class="alert err" role="alert">{{ $errors->first() }}</div>
@endif

<form class="card" method="POST" enctype="multipart/form-data" action="{{ $scholar ? route('panel.scholars.update', $scholar->id) : route('panel.scholars.store') }}" style="max-width:720px">
    @csrf
    @if ($scholar) @method('PUT') @endif

    <div class="field">
        <label for="name">نام</label>
        <input id="name" type="text" name="name" maxlength="120" required placeholder="مثلاً شیخ ضیایی" value="{{ old('name', $scholar->name ?? '') }}">
    </div>
    <div class="field">
        <label for="slug">شناسه (انگلیسی)</label>
        <input id="slug" type="text" name="slug" maxlength="40" required dir="ltr" pattern="[a-z0-9_-]+" placeholder="ziaei" value="{{ old('slug', $scholar->slug ?? '') }}">
        <small class="muted">فقط حروف کوچک انگلیسی، عدد، خط تیره و زیرخط. برنامه با این شناسه استاد را می‌شناسد؛ بعداً تغییرش ندهید.</small>
    </div>
    <div class="field">
        <label for="tagline">زیرعنوان</label>
        <input id="tagline" type="text" name="tagline" maxlength="160" placeholder="سخنرانی‌ها و دروس" value="{{ old('tagline', $scholar->tagline ?? 'سخنرانی‌ها و دروس') }}">
    </div>
    <div class="field">
        <label for="bio">معرفی کوتاه</label>
        <textarea id="bio" name="bio" maxlength="5000">{{ old('bio', $scholar->bio ?? '') }}</textarea>
    </div>
    <div class="field">
        <label for="photo">عکس</label>
        <div style="display:flex;align-items:center;gap:14px">
            <img id="photo-preview" alt=""
                 src="{{ !empty($scholar?->photo_path) ? asset('storage/' . $scholar->photo_path) : '' }}"
                 style="width:88px;height:88px;border-radius:22px;object-fit:cover;background:#eee;{{ empty($scholar?->photo_path) ? 'display:none' : '' }}">
            <div style="flex:1">
                <input id="photo" type="file" name="photo" accept="image/jpeg,image/png,image/webp"
                       onchange="const f=this.files[0],p=document.getElementById('photo-preview');if(f){p.src=URL.createObjectURL(f);p.style.display='';}">
                <small class="muted">jpg، png یا webp تا ۳ مگابایت. عکس مربعی با چهره در مرکز بهترین نتیجه را دارد. بدون عکس، برنامه جلد رنگی می‌سازد.</small>
                @if (!empty($scholar?->photo_path))
                    <label class="check" style="margin-top:8px">
                        <input type="checkbox" name="remove_photo" value="1"> حذف عکس فعلی
                    </label>
                @endif
            </div>
        </div>
    </div>
    <div class="field">
        <label for="hue">رنگ جلد در برنامه</label>
        @php($hue = (int) old('hue', $scholar->hue ?? 150))
        <div style="display:flex;align-items:center;gap:12px">
            <input id="hue" type="range" name="hue" min="0" max="360" value="{{ $hue }}" style="flex:1"
                   oninput="document.getElementById('hue-swatch').style.background='hsl('+this.value+',45%,40%)';document.getElementById('hue-value').textContent=this.value">
            <span id="hue-swatch" aria-hidden="true" style="width:32px;height:32px;border-radius:10px;background:hsl({{ $hue }},45%,40%)"></span>
            <span id="hue-value" class="muted" dir="ltr">{{ $hue }}</span>
        </div>
    </div>
    <div class="field">
        <label class="check">
            <input type="checkbox" name="published" value="1" @checked(old('published', $scholar->published ?? true))>
            در برنامه نمایش داده شود
        </label>
    </div>
    <button class="btn primary" type="submit">ذخیره</button>
</form>
@endsection
