<?php

namespace App\Services\StoreStats;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Reads the install count shown on the app's public Cafe Bazaar page. The number is part of the
 * server-rendered HTML, so no API token or JavaScript is needed (Bazaar's Pishkhan API only manages
 * releases and has no statistics).
 */
class BazaarInstallProvider implements StoreInstallProvider
{
    public function key(): string
    {
        return 'bazaar';
    }

    public function label(): string
    {
        return 'کافه‌بازار';
    }

    public function installs(): ?int
    {
        $url = 'https://cafebazaar.ir/app/' . config('stores.package');

        try {
            $response = Http::timeout(10)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; NourAdhkarDashboard/1.0)', 'Accept-Language' => 'fa'])
                ->get($url);
        } catch (\Throwable $e) {
            Log::warning('Bazaar install count request failed: ' . $e->getMessage());
            return null;
        }

        if (!$response->successful()) {
            Log::warning('Bazaar install count request returned HTTP ' . $response->status());
            return null;
        }

        $installs = self::parseInstalls($response->body());
        if ($installs === null) {
            Log::warning('Bazaar install count was not found on the page; its layout may have changed.');
        }

        return $installs;
    }

    /**
     * Finds the «نصب» (installs) cell of the page's info table:
     * <td class="InfoCube__title">نصب</td><td class="InfoCube__content"><!--[-->۵۵۰ <!--]--></td>
     */
    public static function parseInstalls(string $html): ?int
    {
        if (!preg_match('~InfoCube__title[^>]*>\s*نصب\s*</td>\s*<td[^>]*>(.*?)</td>~su', $html, $match)) {
            return null;
        }

        return self::parseCount(strip_tags(preg_replace('/<!--.*?-->/s', '', $match[1])));
    }

    /** Turns text such as «۵۵۰», «۱٬۲۵۰», «۱۲ هزار» or «۱٫۵ میلیون» into a whole number. */
    public static function parseCount(string $text): ?int
    {
        $text = strtr($text, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
            '٬' => '', ',' => '', '،' => '', '٫' => '.', '+' => '',
        ]);
        $text = trim(preg_replace('/[\s\x{200c}\x{200e}\x{200f}]+/u', ' ', $text));

        if (!preg_match('/^(\d+(?:\.\d+)?)\s*(هزار|میلیون|k|m)?$/iu', $text, $match)) {
            return null;
        }

        $multiplier = match (mb_strtolower($match[2] ?? '')) {
            'هزار', 'k' => 1_000,
            'میلیون', 'm' => 1_000_000,
            default => 1,
        };

        return (int) round((float) $match[1] * $multiplier);
    }
}
