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
     * New installs per Tehran calendar day for the last [$days] days, oldest first:
     * ['days' => ['2026-10-01', …], 'stores' => ['bazaar' => [12, null, …], …]].
     * A day's count is its last total minus the previous day's last total; null where there is not
     * enough history yet. Store corrections that lower a total count as 0, not negative installs.
     */
    public function daily(int $days = 14): array
    {
        $zone = 'Asia/Tehran';
        $today = now($zone)->startOfDay();
        $labels = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $labels[] = $today->copy()->subDays($i)->toDateString();
        }
        $from = $today->copy()->subDays($days)->utc(); // one extra day as the baseline

        $stores = [];
        foreach (array_keys($this->providers()) as $key) {
            $endOfDay = [];
            DB::table('store_install_snapshots')
                ->where('store', $key)->where('recorded_at', '>=', $from)
                ->orderBy('recorded_at')->orderBy('id')
                ->get(['installs', 'recorded_at'])
                ->each(function ($row) use (&$endOfDay, $zone) {
                    $day = \Carbon\Carbon::parse($row->recorded_at, 'UTC')->setTimezone($zone)->toDateString();
                    $endOfDay[$day] = (int) $row->installs; // rows are ordered, so the last one wins
                });
            // The latest snapshot before the window seeds the first day's baseline.
            $before = DB::table('store_install_snapshots')->where('store', $key)->where('recorded_at', '<', $from)
                ->orderByDesc('recorded_at')->orderByDesc('id')->value('installs');

            $previous = $before !== null ? (int) $before : ($endOfDay[$today->copy()->subDays($days)->toDateString()] ?? null);
            $counts = [];
            foreach ($labels as $day) {
                $total = $endOfDay[$day] ?? $previous; // no snapshot that day: unchanged total
                $counts[] = ($total !== null && $previous !== null) ? max(0, $total - $previous) : null;
                if ($total !== null) {
                    $previous = $total;
                }
            }
            $stores[$key] = $counts;
        }

        return ['days' => $labels, 'stores' => $stores];
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
