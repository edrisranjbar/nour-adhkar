<?php

namespace App\Services;

use Symfony\Component\Process\Process;

class LectureAudioChunks
{
    public function prepare(string $source, string $directory, float $remaining): array
    {
        if ($remaining < 3) throw new LectureGenerationException('زمان کافی برای بخش‌بندی صوت باقی نماند؛ دوباره تلاش کنید.');
        $process = new Process(['ffmpeg', '-nostdin', '-hide_banner', '-loglevel', 'error', '-i', $source,
            '-vn', '-ac', '1', '-ar', '16000', '-c:a', 'libmp3lame', '-b:a', '16k',
            '-f', 'segment', '-segment_time', '120', $directory.'/part-%05d.mp3']);
        $process->setTimeout(min(15, $remaining));
        try { $process->mustRun(); }
        catch (\Throwable) { throw new LectureGenerationException('حجم صوت از سقف سرویس بیشتر است (HTTP 413). فشرده‌سازی خودکار انجام نشد؛ نصب و دسترسی ffmpeg روی سرور را بررسی کنید.'); }
        $parts = glob($directory.'/part-*.mp3');
        sort($parts);
        if (!$parts) throw new LectureGenerationException('بخش‌بندی فایل صوتی نتیجه‌ای نداشت.');
        return $parts;
    }
}
