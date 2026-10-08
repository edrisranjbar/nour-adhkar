{{--
    Audio section of the lecture form. The current audio (playable, with a copyable URL) sits above two
    ways to replace it: an uploaded file (drop zone with size check) or a direct https link. Field names
    are unchanged: audio_file and audio_url.
--}}
@php
    use App\Support\PanelFormat as F;
    $maxMb = 100;
    $hasAudio = !empty($audio);
    $mode = old('audio_url') ? 'url' : (($audio['uploaded'] ?? true) ? 'file' : 'url');
@endphp

@push('head')
<style>
    .audio-box { border: 1px solid var(--line); border-radius: 14px; padding: 14px; display: flex; flex-direction: column; gap: 12px; }
    .audio-box > .title { font-weight: 700; display: flex; align-items: center; gap: 8px; }
    .audio-current { background: var(--bg); border-radius: 12px; padding: 12px; display: flex; flex-direction: column; gap: 10px; }
    .audio-current .meta { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; font-size: 13px; }
    .audio-current .name { font-weight: 600; direction: ltr; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 100%; }
    .audio-current audio { width: 100%; height: 40px; }
    .copy-row { display: flex; gap: 6px; align-items: stretch; }
    .copy-row input { flex: 1; min-width: 0; font-family: ui-monospace, Consolas, monospace; font-size: 12.5px; direction: ltr; text-align: left; background: var(--surface); }
    .copy-row .btn { min-height: 38px; white-space: nowrap; }
    .copy-row .btn.copied { color: var(--primary); border-color: var(--primary); }
    .audio-modes { display: inline-flex; border: 1px solid var(--line); border-radius: 10px; overflow: hidden; width: fit-content; }
    .audio-modes button { all: unset; cursor: pointer; padding: 6px 14px; font-size: 13px; }
    .audio-modes button[aria-pressed="true"] { background: var(--primary); color: #fff; }
    .audio-modes button:focus-visible { outline: 2px solid var(--primary); outline-offset: -2px; }
    .drop { border: 1.5px dashed var(--line); border-radius: 12px; padding: 18px; text-align: center; cursor: pointer; transition: border-color .15s, background .15s; }
    .drop:hover, .drop.over { border-color: var(--primary); background: var(--primary-soft); }
    .drop input[type=file] { position: absolute; width: 1px; height: 1px; opacity: 0; }
    .drop .picked { font-weight: 600; direction: ltr; margin-top: 4px; }
    .drop .err { color: var(--danger); font-size: 13px; margin-top: 4px; }
</style>
@endpush

<div class="field audio-box" id="audio-box">
    <div class="title">فایل صوتی</div>

    @if ($hasAudio)
        <div class="audio-current" aria-label="صوت فعلی">
            <div class="meta">
                <span class="badge {{ $audio['uploaded'] ? 'ok' : '' }}">{{ $audio['uploaded'] ? 'فایل بارگذاری‌شده' : 'لینک خارجی' }}</span>
                <span class="name" title="{{ $audio['name'] }}">{{ $audio['name'] }}</span>
                @if ($audio['size'])<span class="muted">{{ F::digits(number_format($audio['size'] / 1048576, 1)) }} مگابایت</span>@endif
                @if (!empty($lecture->duration_sec))<span class="muted">{{ F::digits(intdiv($lecture->duration_sec, 60) . ':' . str_pad($lecture->duration_sec % 60, 2, '0', STR_PAD_LEFT)) }}</span>@endif
            </div>
            <audio controls preload="metadata" src="{{ $audio['url'] }}"></audio>
            <div>
                <label for="audio-link" class="muted" style="font-weight:600;font-size:12px;margin-bottom:4px">نشانی صوت</label>
                <div class="copy-row">
                    <input id="audio-link" type="text" readonly value="{{ $audio['url'] }}" onfocus="this.select()" aria-label="نشانی صوت">
                    <button type="button" class="btn sm" data-copy="#audio-link">کپی</button>
                    <a class="btn sm" href="{{ $audio['url'] }}" target="_blank" rel="noopener">باز کردن</a>
                </div>
            </div>
        </div>
        <div class="muted" style="font-size:13px">برای جایگزینی، فایل تازه بارگذاری کنید یا لینک تازه بدهید؛ در غیر این صورت همین صوت می‌ماند.</div>
    @endif

    <div class="audio-modes" role="group" aria-label="روش افزودن صوت">
        <button type="button" data-mode="file" aria-pressed="{{ $mode === 'file' ? 'true' : 'false' }}">بارگذاری فایل</button>
        <button type="button" data-mode="url" aria-pressed="{{ $mode === 'url' ? 'true' : 'false' }}">لینک مستقیم</button>
    </div>

    <div data-panel="file" @if ($mode !== 'file') hidden @endif>
        <label class="drop" id="audio-drop" for="audio_file">
            <input id="audio_file" type="file" name="audio_file" accept=".mp3,.m4a,.aac,.ogg,.oga,.wav,audio/*" data-max="{{ $maxMb * 1048576 }}">
            <div>فایل را این‌جا رها کنید یا <span style="color:var(--primary);font-weight:600">انتخاب کنید</span></div>
            <div class="muted" style="font-size:12px;margin-top:2px">mp3، m4a، aac، ogg یا wav · حداکثر {{ F::digits($maxMb) }} مگابایت</div>
            <div class="picked" id="audio-picked" hidden></div>
            <div class="err" id="audio-err" role="alert" hidden></div>
        </label>
    </div>

    <div data-panel="url" @if ($mode !== 'url') hidden @endif>
        <label for="audio_url">لینک مستقیم صوت (https)</label>
        <input id="audio_url" type="url" name="audio_url" maxlength="500" dir="ltr" placeholder="https://example.com/lecture.mp3"
               value="{{ old('audio_url', ($audio && !$audio['uploaded']) ? $audio['url'] : '') }}">
    </div>
</div>

<script>
(function () {
    var box = document.getElementById('audio-box');
    if (!box) return;
    var fileInput = document.getElementById('audio_file');
    var urlInput = document.getElementById('audio_url');
    var drop = document.getElementById('audio-drop');
    var picked = document.getElementById('audio-picked');
    var err = document.getElementById('audio-err');
    var fa = new Intl.NumberFormat('fa-IR', { maximumFractionDigits: 1 });

    // Switching method clears the other field, so only one source is ever submitted.
    box.querySelectorAll('[data-mode]').forEach(function (button) {
        button.addEventListener('click', function () {
            var mode = button.dataset.mode;
            box.querySelectorAll('[data-mode]').forEach(function (b) { b.setAttribute('aria-pressed', b === button ? 'true' : 'false'); });
            box.querySelectorAll('[data-panel]').forEach(function (p) { p.hidden = p.dataset.panel !== mode; });
            if (mode === 'file') { urlInput.value = ''; }
            else { fileInput.value = ''; showPicked(null); urlInput.value = urlInput.defaultValue; }
        });
    });

    function showPicked(file) {
        err.hidden = true;
        if (!file) { picked.hidden = true; return; }
        if (file.size > Number(fileInput.dataset.max)) {
            fileInput.value = '';
            picked.hidden = true;
            err.textContent = 'حجم فایل ' + fa.format(file.size / 1048576) + ' مگابایت است؛ حداکثر ' + fa.format(Number(fileInput.dataset.max) / 1048576) + ' مگابایت مجاز است.';
            err.hidden = false;
            return;
        }
        picked.textContent = file.name + ' · ' + fa.format(file.size / 1048576) + ' MB';
        picked.hidden = false;
    }
    fileInput.addEventListener('change', function () { showPicked(fileInput.files[0] || null); });
    ['dragenter', 'dragover'].forEach(function (type) {
        drop.addEventListener(type, function (e) { e.preventDefault(); drop.classList.add('over'); });
    });
    ['dragleave', 'drop'].forEach(function (type) {
        drop.addEventListener(type, function (e) { e.preventDefault(); drop.classList.remove('over'); });
    });
    drop.addEventListener('drop', function (e) {
        if (!e.dataTransfer || !e.dataTransfer.files.length) return;
        fileInput.files = e.dataTransfer.files;
        showPicked(fileInput.files[0]);
    });

    // «کپی»: copies the audio URL, with a fallback for browsers without the async clipboard API.
    box.querySelectorAll('[data-copy]').forEach(function (button) {
        button.addEventListener('click', function () {
            var input = document.querySelector(button.dataset.copy);
            var done = function () {
                button.textContent = 'کپی شد ✓';
                button.classList.add('copied');
                setTimeout(function () { button.textContent = 'کپی'; button.classList.remove('copied'); }, 1800);
            };
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(input.value).then(done, function () { input.select(); document.execCommand('copy'); done(); });
            } else {
                input.select(); document.execCommand('copy'); done();
            }
        });
    });
})();
</script>
