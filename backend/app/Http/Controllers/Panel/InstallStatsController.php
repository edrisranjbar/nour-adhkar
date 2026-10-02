<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\StoreStats\StoreStats;
use Illuminate\Support\Facades\DB;

/** JSON feed behind the dashboard's live install chart. */
class InstallStatsController extends Controller
{
    public function __invoke(StoreStats $stats)
    {
        return response()
            ->json([
                'interval' => (int) config('stores.cache_seconds', 600),
                'feedback' => DB::table('app_feedback')->count(),
                'stores' => $stats->current(),
                'series' => $stats->series(7),
            ])
            ->header('Cache-Control', 'no-store');
    }
}
