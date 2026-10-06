@extends('panel.layout')
@section('title', 'صف اینستاگرام')

@push('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if($entry)
        @foreach($entry['css'] ?? [] as $css)
            <link rel="stylesheet" href="{{ asset('instagram/'.$css) }}">
        @endforeach
    @endif
@endpush

@section('content')
    @if($entry)
        <div id="instagram-queue-app">
            <div class="card empty" role="status">در حال دریافت صف اینستاگرام…</div>
        </div>
        <script>window.NOUR_INSTAGRAM_PANEL = true;</script>
        <script type="module" src="{{ asset('instagram/'.$entry['file']) }}"></script>
    @else
        <div class="card"><h1>صف اینستاگرام</h1><p class="muted">فایل‌های این بخش هنوز آماده نیستند. پس از تکمیل استقرار، صفحه را تازه‌سازی کنید.</p></div>
    @endif
@endsection
