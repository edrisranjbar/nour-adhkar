<?php

namespace App\Services\StoreStats;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/** Reads the same public, unauthenticated review feed used by Bazaar's website. */
class BazaarReviews
{
    public function query()
    {
        return DB::table('store_reviews')->where('store', 'bazaar')->where('package', config('stores.package'))
            ->orderByDesc('date_sort')->orderByDesc('review_id');
    }

    public function current(): array
    {
        $package = config('stores.package');
        $status = Cache::remember('store-reviews:bazaar:'.$package, config('stores.cache_seconds', 600), function () use ($package) {
            try {
                $rows = [];
                // Bounded requests: featured reviews may precede newer reviews in the public feed.
                for ($page = 0; $page < 3; $page++) {
                    $response = Http::timeout(10)->acceptJson()->post('https://api.cafebazaar.ir/rest-v1/process/ReviewRequest', [
                        'properties' => ['language' => 2, 'clientVersion' => 'web'],
                        'singleRequest' => ['reviewRequest' => ['packageName' => $package, 'start' => $page * 24, 'end' => ($page + 1) * 24]],
                    ]);
                    $reviews = $response->json('singleReply.reviewReply.reviews');
                    if (! $response->successful() || $response->json('properties.statusCode') !== 200 || ! is_array($reviews) || ! array_is_list($reviews)) {
                        throw new \RuntimeException('Invalid public review response');
                    }
                    foreach ($reviews as $review) {
                        $row = self::normalize($review);
                        if ($row !== null) {
                            $rows[$row['review_id']] = $row;
                        }
                    }
                    if (count($reviews) < 24) {
                        break;
                    }
                }
                DB::transaction(function () use ($rows, $package) {
                    foreach ($rows as $row) {
                        DB::table('store_reviews')->upsert([
                            $row + ['store' => 'bazaar', 'package' => $package, 'first_seen_at' => now(), 'last_seen_at' => now()],
                        ], ['store', 'package', 'review_id'], ['author', 'message', 'rating', 'date_label', 'date_sort', 'last_seen_at']);
                    }
                });

                return ['stale' => false, 'checked_at' => now()->timestamp, 'updated_at' => now()->timestamp];
            } catch (\Throwable $e) {
                Log::warning('Bazaar reviews could not be refreshed: '.$e->getMessage());

                return ['stale' => true, 'checked_at' => now()->timestamp, 'updated_at' => null];
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
        if (! is_array($review) || ! isset($review['id'], $review['comment']) || ! is_scalar($review['id']) || ! is_string($review['comment'])) {
            throw new \RuntimeException('Invalid review fields');
        }
        if (! preg_match('/^\d{1,60}$/', (string) $review['id'])) {
            throw new \RuntimeException('Invalid review ID');
        }
        if (trim($review['comment']) === '' || ($review['fromDeveloper'] ?? false)) {
            return null;
        }
        $date = is_string($review['date'] ?? null) ? mb_substr($review['date'], 0, 40) : null;
        $normalized = strtr($date ?? '', array_combine(
            preg_split('//u', '۰۱۲۳۴۵۶۷۸۹٠١٢٣٤٥٦٧٨٩', -1, PREG_SPLIT_NO_EMPTY), str_split('01234567890123456789')
        ));
        $sort = preg_match('~^\d{4}/(?:0[1-9]|1[0-2])/(?:0[1-9]|[12]\d|3[01])$~', $normalized) ? $normalized : null;
        $rating = $review['rate'] ?? null;

        return [
            'review_id' => (string) $review['id'],
            'author' => is_string($review['user'] ?? null) ? mb_substr($review['user'], 0, 255) : null,
            'message' => trim($review['comment']),
            'rating' => is_int($rating) && $rating >= 1 && $rating <= 5 ? $rating : null,
            'date_label' => $date, 'date_sort' => $sort,
        ];
    }
}
