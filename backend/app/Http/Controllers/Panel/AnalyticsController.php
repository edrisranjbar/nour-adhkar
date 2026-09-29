<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Support\Analytics\Report;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __invoke(Request $request)
    {
        $days = (int) $request->query('range', 30);
        $days = array_key_exists($days, Report::RANGES) ? $days : 30;
        $tab = $request->query('tab') === 'app' ? 'app' : 'web';
        $report = new Report($days);

        return view('panel.analytics.index', [
            'report' => $report,
            'tab' => $tab,
            'data' => $tab === 'app' ? $report->app() : $report->web(),
        ]);
    }
}
