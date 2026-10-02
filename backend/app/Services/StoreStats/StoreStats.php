<?php

namespace App\Services\StoreStats;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Install counts per store for the admin dashboard: fetches them (at most once per cache window),
 * remembers every change as a snapshot, and serves the history for the chart.
 */
class StoreStats
{
    private const CHART_POINTS = 300;

    /** @return array<string, StoreInstallProvider> keyed by provider key, in configured order */
    public function providers(): array
    {
        $providers = [];
        foreach (config('stores.providers', []) as $class) {
            $provider = app($class);
            $providers[$provider->key()] = $provider;
        }

        return $providers;
    }

    /**
     * Current numbers. When a store cannot be read, the last known number is returned and marked stale.
     *
     * @return array<string, array<string, mixed>>
     */
    public function current(): array
    {
        $result = [];

        foreach ($this->providers() as $key => $provider) {
            $fresh = Cache::remember("store-metrics:$key", config('stores.cache_seconds', 600), function () use ($provider, $key) {
                $metrics = $provider->metrics();
                $installs = $metrics['installs'];
                if ($installs !== null) {
                    $this->record($key, $installs);
                }
                if ($metrics['rating'] !== null && $metrics['rating_count'] !== null) {
                    $this->recordRating($key, $metrics['rating'], $metrics['rating_count']);
                }

                return $metrics + ['at' => now()->timestamp];
            });

            $rating = [
                'rating' => $fresh['rating'],
                'rating_count' => $fresh['rating_count'],
                'rating_stale' => false,
                'rating_updated_at' => $fresh['at'],
            ];
            if ($fresh['rating'] === null || $fresh['rating_count'] === null) {
                $lastRating = DB::table('store_rating_snapshots')->where('store', $key)->orderByDesc('recorded_at')->orderByDesc('id')->first();
                $rating = [
                    'rating' => $lastRating ? (float) $lastRating->rating : null,
                    'rating_count' => $lastRating ? (int) $lastRating->rating_count : null,
                    'rating_stale' => true,
                    'rating_updated_at' => $lastRating ? \Carbon\Carbon::parse($lastRating->recorded_at)->timestamp : null,
                ];
            }

            if ($fresh['installs'] !== null) {
                $result[$key] = ['label' => $provider->label(), 'installs' => $fresh['installs'], 'stale' => false, 'updated_at' => $fresh['at']] + $rating;
                continue;
            }

            $last = DB::table('store_install_snapshots')->where('store', $key)->orderByDesc('recorded_at')->orderByDesc('id')->first();
            $result[$key] = [
                'label' => $provider->label(),
                'installs' => $last ? (int) $last->installs : null,
                'stale' => true,
                'updated_at' => $last ? \Carbon\Carbon::parse($last->recorded_at)->timestamp : null,
            ] + $rating;
        }

        return $result;
    }

    /**
     * Chart points per store, oldest first, as [unix time, installs] pairs.
     *
     * @return array<string, list<array{0: int, 1: int}>>
     */
    public function series(int $days = 7): array
    {
        $series = [];

        foreach (array_keys($this->providers()) as $key) {
            $rows = DB::table('store_install_snapshots')
                ->where('store', $key)
                ->where('recorded_at', '>=', now()->subDays($days))
                ->orderBy('recorded_at')->orderBy('id')
                ->get(['installs', 'recorded_at']);

            $points = $rows->map(fn ($row) => [\Carbon\Carbon::parse($row->recorded_at)->timestamp, (int) $row->installs])->all();

            if (count($points) > self::CHART_POINTS) {
                $step = (int) ceil(count($points) / self::CHART_POINTS);
                $last = end($points);
                $points = array_values(array_filter($points, fn ($point, $i) => $i % $step === 0, ARRAY_FILTER_USE_BOTH));
                if (end($points) !== $last) {
                    $points[] = $last;
                }
            }

            $series[$key] = $points;
        }

        return $series;
    }

    /** Stores a snapshot when the number changed or the last one is older than the heartbeat. */
    private function record(string $key, int $installs): void
    {
        $last = DB::table('store_install_snapshots')->where('store', $key)->orderByDesc('recorded_at')->orderByDesc('id')->first();

        $stale = !$last || \Carbon\Carbon::parse($last->recorded_at)->lte(now()->subMinutes((int) config('stores.heartbeat_minutes', 10)));
        if ($last && (int) $last->installs === $installs && !$stale) {
            return;
        }

        DB::table('store_install_snapshots')->insert(['store' => $key, 'installs' => $installs, 'recorded_at' => now()]);
    }

    private function recordRating(string $key, float $rating, int $count): void
    {
        $last = DB::table('store_rating_snapshots')->where('store', $key)->orderByDesc('recorded_at')->orderByDesc('id')->first();
        $stale = !$last || \Carbon\Carbon::parse($last->recorded_at)->lte(now()->subMinutes((int) config('stores.heartbeat_minutes', 10)));
        if ($last && (float) $last->rating === $rating && (int) $last->rating_count === $count && !$stale) {
            return;
        }
        DB::table('store_rating_snapshots')->insert([
            'store' => $key, 'rating' => $rating, 'rating_count' => $count, 'recorded_at' => now(),
        ]);
    }
}
