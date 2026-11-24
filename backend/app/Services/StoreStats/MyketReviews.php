<?php

namespace App\Services\StoreStats;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/** Public listing comments only; not the full developer-panel review feed. */
class MyketReviews
{
    public function query()
    {
        return DB::table('store_reviews')->where('store', 'myket')->where('package', config('stores.package'))
            ->orderByDesc('first_seen_at')->orderByDesc('review_id');
    }

    public function current(): array
    {
        $status = Cache::remember('store-reviews:myket:'.config('stores.package'), config('stores.cache_seconds', 600), function () {
            try {
                $app = MyketInstallProvider::listing(MyketInstallProvider::page());
                if (!isset($app['review']) || !is_array($app['review']) || !array_is_list($app['review'])) {
                    throw new \RuntimeException('Myket listing review data is missing');
                }
                $rows = array_filter(array_map(self::normalize(...), $app['review']));
                DB::transaction(function () use ($rows) {
                    foreach ($rows as $row) {
                        DB::table('store_reviews')->upsert([$row + [
                            'store' => 'myket', 'package' => config('stores.package'),
                            'first_seen_at' => now(), 'last_seen_at' => now(),
                        ]], ['store', 'package', 'review_id'], ['author', 'message', 'rating', 'last_seen_at']);
                    }
                });
                return ['stale' => false, 'updated_at' => now()->timestamp];
            } catch (\Throwable $e) {
                Log::warning('Myket reviews unavailable: '.$e->getMessage());
                return ['stale' => true, 'updated_at' => null];
            }
        });
        if ($status['updated_at'] === null) {
            $last = $this->query()->max('last_seen_at');
            $status['updated_at'] = $last ? \Carbon\Carbon::parse($last)->timestamp : null;
        }
        return $status + ['count' => $this->query()->count()];
    }

    public static function normalize(mixed $review): ?array
    {
        if (!is_array($review) || !is_string($review['reviewBody'] ?? null)) {
            throw new \RuntimeException('Invalid Myket review');
        }
        $message = trim($review['reviewBody']);
        if ($message === '') return null;
        $author = is_string($review['author']['name'] ?? null) ? $review['author']['name'] : null;
        $rating = $review['reviewRating']['ratingValue'] ?? null;
        return ['review_id' => substr(hash('sha256', ($author ?? '')."\0".$message), 0, 60),
            'author' => $author ? mb_substr($author, 0, 255) : null, 'message' => $message,
            'rating' => is_numeric($rating) && $rating >= 1 && $rating <= 5 && floor((float)$rating) == $rating ? (int)$rating : null,
            'date_label' => null, 'date_sort' => null];
    }
}
