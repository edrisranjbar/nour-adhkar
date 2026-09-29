<?php

namespace App\Support\Analytics;

use Illuminate\Http\Request;

class Visitor
{
    private const BOT_PATTERN = '/bot|crawl|spider|slurp|headless|lighthouse|preview|facebookexternalhit|embedly|curl|wget|python-requests|httpclient|monitor/i';

    /**
     * Anonymous visitor key that changes every day (Tehran time), so visitors can be
     * counted per day without storing IPs or tracking anyone across days.
     */
    public static function hash(Request $request, ?string $userAgent): string
    {
        $day = now('Asia/Tehran')->toDateString();

        return hash('sha256', $day.'|'.$request->ip().'|'.($userAgent ?? '').'|'.config('app.key'));
    }

    public static function isBot(?string $userAgent): bool
    {
        return !$userAgent || preg_match(self::BOT_PATTERN, $userAgent) === 1;
    }

    /** @return array{browser: ?string, os: ?string, device: string} */
    public static function parse(?string $userAgent): array
    {
        $ua = strtolower($userAgent ?? '');

        // Order matters: Edge, Opera and Samsung Internet also contain "chrome"; Chrome contains "safari".
        $browser = match (true) {
            str_contains($ua, 'edg/') || str_contains($ua, 'edge/') => 'Edge',
            str_contains($ua, 'opr/') || str_contains($ua, 'opera') => 'Opera',
            str_contains($ua, 'samsungbrowser') => 'Samsung Internet',
            str_contains($ua, 'firefox') || str_contains($ua, 'fxios') => 'Firefox',
            str_contains($ua, 'chrome') || str_contains($ua, 'crios') => 'Chrome',
            str_contains($ua, 'safari') => 'Safari',
            default => null,
        };

        $os = match (true) {
            str_contains($ua, 'android') => 'Android',
            str_contains($ua, 'iphone') || str_contains($ua, 'ipad') || str_contains($ua, 'ipod') => 'iOS',
            str_contains($ua, 'windows') => 'Windows',
            str_contains($ua, 'mac os') || str_contains($ua, 'macintosh') => 'macOS',
            str_contains($ua, 'cros') => 'ChromeOS',
            str_contains($ua, 'linux') => 'Linux',
            default => null,
        };

        $device = match (true) {
            str_contains($ua, 'ipad') || str_contains($ua, 'tablet') || (str_contains($ua, 'android') && !str_contains($ua, 'mobile')) => 'tablet',
            str_contains($ua, 'mobile') || str_contains($ua, 'iphone') => 'mobile',
            default => 'desktop',
        };

        return ['browser' => $browser, 'os' => $os, 'device' => $device];
    }

    public static function referrerHost(?string $referrer): ?string
    {
        if (!$referrer) {
            return null;
        }
        $host = parse_url($referrer, PHP_URL_HOST);
        if (!$host) {
            return null;
        }
        $host = preg_replace('/^(www\.|m\.)/i', '', strtolower($host));
        $own = preg_replace('/^www\./i', '', strtolower((string) parse_url((string) config('app.frontend_url', ''), PHP_URL_HOST)));

        // Internal navigation is not a referral.
        return ($own && $host === $own) || $host === 'adhkar.ir' ? null : mb_substr($host, 0, 120);
    }
}
