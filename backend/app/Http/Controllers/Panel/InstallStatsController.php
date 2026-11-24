<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\StoreStats\BazaarReviews;
use App\Services\StoreStats\MyketReviews;
use App\Services\StoreStats\StoreStats;
use Illuminate\Support\Facades\DB;

/** JSON feed behind the dashboard's live install chart. */
class InstallStatsController extends Controller
{
    public function __invoke(StoreStats $stats, BazaarReviews $reviews, MyketReviews $myket)
    {
        $bazaarStatus = $reviews->current();
        $stores = $stats->current();
        $myketStatus = $myket->current();

        return response()
            ->json([
                'interval' => (int) config('stores.cache_seconds', 600),
                'feedback' => DB::table('app_feedback')->count(),
                'bazaar_reviews' => $bazaarStatus,
                'bazaar_reviews_html' => view('panel.feedback._bazaar', [
                    'bazaarStatus' => $bazaarStatus, 'bazaarItems' => $reviews->query()->limit(5)->get(), 'compact' => true,
                ])->render(),
                'stores' => $stores,
                'rating_summary' => StoreStats::ratingSummary($stores),
                'myket_reviews_html' => view('panel.feedback._myket', [
                    'myketStatus' => $myketStatus, 'myketItems' => $myket->query()->limit(5)->get(), 'compact' => true,
                ])->render(),
                'total' => StoreStats::completeTotal(array_column($stores, 'installs')),
                'daily' => $stats->daily(14),
            ])
            ->header('Cache-Control', 'no-store');
    }
}
