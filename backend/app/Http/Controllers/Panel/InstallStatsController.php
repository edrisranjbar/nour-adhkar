<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\StoreStats\BazaarReviews;
use App\Services\StoreStats\StoreStats;
use Illuminate\Support\Facades\DB;

/** JSON feed behind the dashboard's live install chart. */
class InstallStatsController extends Controller
{
    public function __invoke(StoreStats $stats, BazaarReviews $reviews)
    {
        $bazaarStatus = $reviews->current();

        return response()
            ->json([
                'interval' => (int) config('stores.cache_seconds', 600),
                'feedback' => DB::table('app_feedback')->count(),
                'bazaar_reviews' => $bazaarStatus,
                'bazaar_reviews_html' => view('panel.feedback._bazaar', [
                    'bazaarStatus' => $bazaarStatus, 'bazaarItems' => $reviews->query()->limit(5)->get(), 'compact' => true,
                ])->render(),
                'stores' => $stats->current(),
                'daily' => $stats->daily(14),
            ])
            ->header('Cache-Control', 'no-store');
    }
}
