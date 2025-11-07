<?php

namespace App\Http\Controllers;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AnalyticsController extends Controller
{
    public function track(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'path' => 'required|string|max:255',
            'referrer' => 'nullable|string|max:255',
            'ua' => 'nullable|string|max:512',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $data = $validator->validated();
            $ip = $request->ip();
            $userAgent = $data['ua'] ?? $request->userAgent();

            // Basic UA parse (very naive)
            $browser = null;
            if ($userAgent) {
                $ua = strtolower($userAgent);
                $browser = str_contains($ua, 'chrome') ? 'Chrome'
                    : (str_contains($ua, 'firefox') ? 'Firefox'
                    : (str_contains($ua, 'safari') ? 'Safari'
                    : (str_contains($ua, 'edge') ? 'Edge'
                    : (str_contains($ua, 'opera') || str_contains($ua, 'opr') ? 'Opera'
                    : null))));
            }

            // Country detection: use Cloudflare or Proxy headers if present
            $country = $request->header('CF-IPCountry');
            $countryCode = $country ? strtoupper($country) : null;

            DB::table('page_visits')->insert([
                'path' => $data['path'],
                'referrer' => $data['referrer'] ?? null,
                'ip' => $ip,
                'user_agent' => $userAgent,
                'browser' => $browser,
                'country' => null,
                'country_code' => $countryCode,
                'visited_at' => now(),
            ]);

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false], 500);
        }
    }

    public function overview(Request $request)
    {
        $days = (int) ($request->input('days', 14));
        $days = $days > 60 ? 60 : ($days < 1 ? 14 : $days);

        $defaultPayload = [
            'total' => 0,
            'uniqueVisitors' => 0,
            'daily' => collect(),
            'topPages' => collect(),
            'browsers' => collect(),
            'countries' => collect(),
        ];

        try {
            $startDate = now()->subDays($days - 1)->startOfDay();

            // Daily visits
            $daily = DB::table('page_visits')
                ->selectRaw('DATE(visited_at) as date, COUNT(*) as visits')
                ->where('visited_at', '>=', $startDate)
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // Top pages
            $topPages = DB::table('page_visits')
                ->selectRaw('path, COUNT(*) as visits')
                ->where('visited_at', '>=', $startDate)
                ->groupBy('path')
                ->orderByDesc('visits')
                ->limit(10)
                ->get();

            // Totals
            $total = DB::table('page_visits')->where('visited_at', '>=', $startDate)->count();
            $uniqueVisitors = DB::table('page_visits')->where('visited_at', '>=', $startDate)->distinct('ip')->count('ip');

            // Browsers
            $browsers = DB::table('page_visits')
                ->selectRaw('COALESCE(browser, "Unknown") as browser, COUNT(*) as visits')
                ->where('visited_at', '>=', $startDate)
                ->groupBy('browser')
                ->orderByDesc('visits')
                ->get();

            // Countries (based on code header)
            $countries = DB::table('page_visits')
                ->selectRaw('COALESCE(country_code, "--") as code, COUNT(*) as visits')
                ->where('visited_at', '>=', $startDate)
                ->groupBy('code')
                ->orderByDesc('visits')
                ->limit(20)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $total,
                    'uniqueVisitors' => $uniqueVisitors,
                    'daily' => $daily,
                    'topPages' => $topPages,
                    'browsers' => $browsers,
                    'countries' => $countries,
                ],
            ]);
        } catch (QueryException $exception) {
            $message = $exception->getMessage();
            $isMissingAnalyticsTable = str_contains(strtolower($message), 'page_visits')
                && (str_contains(strtolower($message), 'no such table')
                    || str_contains(strtolower($message), 'doesn\'t exist')
                    || str_contains(strtolower($message), 'base table or view not found'));

            if ($isMissingAnalyticsTable) {
                Log::warning('Analytics overview requested but analytics tables are not available.', [
                    'sql_state' => $exception->getSqlState(),
                    'code' => $exception->getCode(),
                ]);

                return response()->json([
                    'success' => true,
                    'data' => $defaultPayload,
                    'meta' => ['fallback' => true],
                ]);
            }

            Log::error('Failed to fetch analytics overview.', [
                'error' => $message,
                'sql_state' => $exception->getSqlState(),
                'code' => $exception->getCode(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to load analytics overview.',
            ], 500);
        }
    }
}


