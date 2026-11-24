<?php

namespace App\Services\StoreStats;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MyketInstallProvider implements StoreInstallProvider
{
    public function key(): string
    {
        return 'myket';
    }

    public function label(): string
    {
        return 'مایکت';
    }

    public function installs(): ?int
    {
        return $this->metrics()['installs'];
    }

    public function metrics(): array
    {
        $installs = null;
        try {
            $html = self::page();
            $installs = self::parseInstalls($html);
            $app = self::listing($html);
            $aggregate = $app['aggregateRating'] ?? [];
            $rating = $aggregate['ratingValue'] ?? null;
            $count = $aggregate['ratingCount'] ?? null;
            $valid = is_numeric($rating) && $rating >= 0 && $rating <= 5
                && is_numeric($count) && $count >= 0 && floor((float) $count) == $count;
            return ['installs' => $installs,
                'rating' => $valid ? (float) $rating : null, 'rating_count' => $valid ? (int) $count : null];
        } catch (\Throwable $e) {
            Log::warning('Myket listing unavailable: '.$e->getMessage());
            return ['installs' => $installs, 'rating' => null, 'rating_count' => null];
        }
    }

    public static function page(): string
    {
        return \Illuminate\Support\Facades\Cache::remember('myket-listing:'.config('stores.package'),
            config('stores.cache_seconds', 600), fn () => Http::timeout(10)
                ->get('https://myket.ir/app/'.rawurlencode(config('stores.package')))->throw()->body());
    }

    public static function listing(string $html): array
    {
        preg_match_all('~<script\b[^>]*type=["\x27]application/ld\+json["\x27][^>]*>(.*?)</script>~si', $html, $matches);
        foreach ($matches[1] as $json) {
            $app = json_decode($json, true);
            if (is_array($app) && ($app['@type'] ?? null) === 'SoftwareApplication') {
                return $app;
            }
        }
        throw new \RuntimeException('Myket application metadata is missing');
    }
    public static function parseInstalls(string $html): ?int
    {
        $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if (!preg_match('~<td[^>]*>\s*تعداد دانلود\s*</td>\s*<td[^>]*>(.*?)</td>~su', $html, $match)) {
            return null;
        }

        return BazaarInstallProvider::parseCount(strip_tags($match[1]));
    }
}
