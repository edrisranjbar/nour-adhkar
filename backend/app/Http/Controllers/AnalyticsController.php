<?php

namespace App\Http\Controllers;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Support\Analytics\Visitor;

class AnalyticsController extends Controller
{
    // Landing-page actions the site may report; anything else is rejected.
    public const EVENTS = ['store_click', 'donate_click', 'github_click', 'video_play', 'video_unmute', 'lang_switch', 'theme_toggle', 'section_view'];

    public function track(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'path' => 'required|string|max:255',
            'referrer' => 'nullable|string|max:255',
            'ua' => 'nullable|string|max:512',
            'session_id' => 'nullable|string|max:64',
            'lang' => 'nullable|string|max:8',
            'utm_source' => 'nullable|string|max:100',
            'utm_medium' => 'nullable|string|max:100',
            'utm_campaign' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $data = $validator->validated();
            $userAgent = $data['ua'] ?? $request->userAgent();

            if (Visitor::isBot($userAgent)) {
                return response()->json(['success' => true]);
            }

            $client = Visitor::parse($userAgent);
            $country = $request->header('CF-IPCountry');

            DB::table('page_visits')->insert([
                'path' => $data['path'],
                'referrer' => $data['referrer'] ?? null,
                'referrer_host' => Visitor::referrerHost($data['referrer'] ?? null),
                'ip' => null,
                'visitor_hash' => Visitor::hash($request, $userAgent),
                'session_id' => $data['session_id'] ?? null,
                'user_agent' => $userAgent,
                'browser' => $client['browser'],
                'os' => $client['os'],
                'device' => $client['device'],
                'lang' => isset($data['lang']) ? strtolower($data['lang']) : null,
                'country' => null,
                'country_code' => $country ? strtoupper(substr($country, 0, 2)) : null,
                'utm_source' => $data['utm_source'] ?? null,
                'utm_medium' => $data['utm_medium'] ?? null,
                'utm_campaign' => $data['utm_campaign'] ?? null,
                'visited_at' => now(),
            ]);

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false], 500);
        }
    }

    public function event(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', Rule::in(self::EVENTS)],
            'label' => 'nullable|string|max:100',
            'path' => 'nullable|string|max:255',
            'session_id' => 'nullable|string|max:64',
        ]);

        $userAgent = $request->userAgent();
        if (!Visitor::isBot($userAgent)) {
            DB::table('analytics_events')->insert([
                'name' => $data['name'],
                'label' => $data['label'] ?? null,
                'path' => $data['path'] ?? null,
                'visitor_hash' => Visitor::hash($request, $userAgent),
                'session_id' => $data['session_id'] ?? null,
                'created_at' => now(),
            ]);
        }

        return response()->json(['success' => true]);
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
            // Raw IPs are no longer stored; count the daily visitor hash (older rows fall back to their IP).
            $uniqueVisitors = (int) DB::table('page_visits')->where('visited_at', '>=', $startDate)
                ->selectRaw('COUNT(DISTINCT COALESCE(visitor_hash, ip)) as c')->value('c');

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


