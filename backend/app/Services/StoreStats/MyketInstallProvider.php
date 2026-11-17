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
        try {
            $response = Http::timeout(10)->get('https://myket.ir/app/'.rawurlencode(config('stores.package')));
            $count = $response->successful() ? self::parseInstalls($response->body()) : null;
        } catch (ConnectionException) {
            $count = null;
        }

        if ($count === null) {
            Log::warning('Myket public download count is unavailable or not numeric.');
        }

        return $count;
    }

    public function metrics(): array
    {
        return ['installs' => $this->installs(), 'rating' => null, 'rating_count' => null];
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
