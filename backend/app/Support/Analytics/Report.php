<?php

namespace App\Support\Analytics;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Builds the admin analytics page. Days and hours are bucketed in Tehran time.
 * Aggregation happens in PHP (one pass over the period) so it behaves the same on SQLite and MySQL.
 */
class Report
{
    public const TZ = 'Asia/Tehran';
    public const RANGES = [1 => 'امروز', 7 => '۷ روز', 30 => '۳۰ روز', 90 => '۹۰ روز'];

    public readonly CarbonImmutable $start;
    public readonly CarbonImmutable $end;
    public readonly CarbonImmutable $prevStart;

    public function __construct(public readonly int $days)
    {
        $this->end = CarbonImmutable::now(self::TZ);
        $this->start = $this->end->startOfDay()->subDays($days - 1);
        $this->prevStart = $this->start->subDays($days);
    }

    /** Tehran calendar days of the period, oldest first, as Y-m-d keys. */
    public function dayKeys(): array
    {
        $keys = [];
        for ($d = $this->start; $d <= $this->end; $d = $d->addDay()) {
            $keys[] = $d->toDateString();
        }
        return $keys;
    }

    public function web(): array
    {
        $days = array_fill_keys($this->dayKeys(), ['visits' => 0, 'visitors' => []]);
        $heat = array_fill(0, 7, array_fill(0, 24, 0));
        $hours = array_fill(0, 24, ['visits' => 0, 'visitors' => []]);
        $sessions = [];
        $breakdowns = ['pages' => [], 'referrers' => [], 'sources' => [], 'campaigns' => [], 'devices' => [], 'os' => [], 'browsers' => [], 'langs' => [], 'countries' => []];
        $visits = 0;

        foreach ($this->visitRows($this->start) as $row) {
            $at = CarbonImmutable::parse($row->visited_at, 'UTC')->setTimezone(self::TZ);
            $day = $at->toDateString();
            if (!isset($days[$day])) {
                continue;
            }
            $visits++;
            $days[$day]['visits']++;
            $days[$day]['visitors'][$this->visitorKey($row)] = true;
            $heat[$at->dayOfWeek][$at->hour]++;
            $hours[$at->hour]['visits']++;
            $hours[$at->hour]['visitors'][$this->visitorKey($row)] = true;
            $sessionKey = $row->session_id ?: 'v'.$row->id;
            $sessions[$sessionKey] = ($sessions[$sessionKey] ?? 0) + 1;

            $this->bump($breakdowns['pages'], $row->path);
            $this->bump($breakdowns['referrers'], $row->referrer_host ?: 'direct');
            $this->bump($breakdowns['sources'], $row->utm_source);
            $this->bump($breakdowns['campaigns'], $row->utm_campaign);
            $this->bump($breakdowns['devices'], $row->device ?: 'unknown');
            $this->bump($breakdowns['os'], $row->os ?: 'unknown');
            $this->bump($breakdowns['browsers'], $row->browser ?: 'unknown');
            $this->bump($breakdowns['langs'], $row->lang ? substr($row->lang, 0, 2) : 'unknown');
            $this->bump($breakdowns['countries'], $row->country_code ?: 'unknown');
        }

        $daily = [];
        $visitors = 0;
        foreach ($days as $key => $d) {
            $count = count($d['visitors']);
            $visitors += $count;
            $daily[] = ['date' => $key, 'visits' => $d['visits'], 'visitors' => $count];
        }
        // A single day is more useful hour by hour; only the hours that have passed are shown.
        if ($this->days === 1) {
            $daily = [];
            foreach (array_slice($hours, 0, $this->end->hour + 1, true) as $h => $d) {
                $daily[] = ['hour' => $h, 'visits' => $d['visits'], 'visitors' => count($d['visitors'])];
            }
        }

        $sessionCount = count($sessions);
        $singlePage = count(array_filter($sessions, fn ($n) => $n === 1));
        $prev = $this->previousWebTotals();
        $events = $this->events();
        $storeVisitors = $events['visitors']['store_click'] ?? 0;

        foreach ($breakdowns as &$list) {
            arsort($list);
        }
        unset($list);

        return [
            'kpis' => [
                'visitors' => [$visitors, $prev['visitors']],
                'visits' => [$visits, $prev['visits']],
                'sessions' => [$sessionCount, $prev['sessions']],
                'pagesPerSession' => [$sessionCount ? round($visits / $sessionCount, 1) : 0, null],
                'bounce' => [$sessionCount ? round($singlePage * 100 / $sessionCount) : 0, null],
                'storeClicks' => [$events['counts']['store_click'] ?? 0, $events['prevCounts']['store_click'] ?? 0],
                'conversion' => [$visitors ? round($storeVisitors * 100 / $visitors, 1) : 0, null],
                'videoPlays' => [$events['counts']['video_play'] ?? 0, $events['prevCounts']['video_play'] ?? 0],
            ],
            'daily' => $daily,
            'heat' => $heat,
            'breakdowns' => array_map(fn ($l) => array_slice($l, 0, 10, true), $breakdowns),
            'events' => $events,
            'funnel' => [
                ['visitors', $visitors],
                ['section_view:features', $events['labelVisitors']['section_view']['features'] ?? 0],
                ['video_play', $events['visitors']['video_play'] ?? 0],
                ['store_click', $storeVisitors],
            ],
            'live' => $this->live(),
            'recent' => DB::table('page_visits')->orderByDesc('visited_at')->limit(12)
                ->get(['path', 'referrer_host', 'device', 'os', 'browser', 'country_code', 'visited_at']),
        ];
    }

    public function app(): array
    {
        $days = array_fill_keys($this->dayKeys(), ['signups' => 0, 'feedback' => 0]);
        $startUtc = $this->start->utc();
        $prevUtc = $this->prevStart->utc();

        foreach (DB::table('users')->where('created_at', '>=', $startUtc)->pluck('created_at') as $at) {
            $key = CarbonImmutable::parse($at, 'UTC')->setTimezone(self::TZ)->toDateString();
            if (isset($days[$key])) {
                $days[$key]['signups']++;
            }
        }

        $hasInbox = Schema::hasTable('app_feedback');
        $feedbackTypes = [];
        if ($hasInbox) {
            foreach (DB::table('app_feedback')->where('created_at', '>=', $startUtc)->get(['type', 'created_at']) as $row) {
                $key = CarbonImmutable::parse($row->created_at, 'UTC')->setTimezone(self::TZ)->toDateString();
                if (isset($days[$key])) {
                    $days[$key]['feedback']++;
                }
                $this->bump($feedbackTypes, $row->type);
            }
            arsort($feedbackTypes);
        }

        $installations = $hasInbox ? DB::table('app_notice_reads')->distinct()->count('installation_id') : 0;
        $notices = $hasInbox
            ? DB::table('app_notices')
                ->leftJoin('app_notice_reads', 'app_notice_reads.notice_id', '=', 'app_notices.id')
                ->where('app_notices.published', true)
                ->groupBy('app_notices.id', 'app_notices.title', 'app_notices.created_at')
                ->orderByDesc('app_notices.created_at')
                ->limit(10)
                ->get(['app_notices.id', 'app_notices.title', 'app_notices.created_at', DB::raw('COUNT(app_notice_reads.id) as reads')])
            : collect();

        $newUsers = DB::table('users')->where('created_at', '>=', $startUtc)->count();
        $prevUsers = DB::table('users')->whereBetween('created_at', [$prevUtc, $startUtc])->count();
        $feedback = array_sum(array_column($days, 'feedback'));
        $prevFeedback = $hasInbox ? DB::table('app_feedback')->whereBetween('created_at', [$prevUtc, $startUtc])->count() : 0;
        $active = $hasInbox ? DB::table('app_notice_reads')->where('read_at', '>=', $startUtc)->distinct()->count('installation_id') : 0;
        $prevActive = $hasInbox ? DB::table('app_notice_reads')->whereBetween('read_at', [$prevUtc, $startUtc])->distinct()->count('installation_id') : 0;

        return [
            'kpis' => [
                'users' => [DB::table('users')->count(), null],
                'newUsers' => [$newUsers, $prevUsers],
                'verified' => [DB::table('users')->whereNotNull('email_verified_at')->count(), null],
                'logins' => [DB::table('users')->where('last_login_at', '>=', $startUtc)->count(), null],
                'installations' => [$installations, null],
                'activeInstallations' => [$active, $prevActive],
                'feedback' => [$feedback, $prevFeedback],
            ],
            'daily' => array_map(fn ($k, $d) => ['date' => $k] + $d, array_keys($days), $days),
            'feedbackTypes' => $feedbackTypes,
            'notices' => $notices,
        ];
    }

    private function events(): array
    {
        $out = ['counts' => [], 'prevCounts' => [], 'visitors' => [], 'labels' => [], 'labelVisitors' => []];
        if (!Schema::hasTable('analytics_events')) {
            return $out;
        }

        $visitorSets = [];
        $labelSets = [];
        foreach (DB::table('analytics_events')->where('created_at', '>=', $this->start->utc())->cursor() as $e) {
            $this->bump($out['counts'], $e->name);
            $visitorSets[$e->name][$e->visitor_hash] = true;
            if ($e->label !== null && $e->label !== '') {
                $this->bump($out['labels'][$e->name], $e->label);
                $labelSets[$e->name][$e->label][$e->visitor_hash] = true;
            }
        }
        $out['visitors'] = array_map('count', $visitorSets);
        $out['labelVisitors'] = array_map(fn ($byLabel) => array_map('count', $byLabel), $labelSets);
        foreach ($out['labels'] as &$l) {
            arsort($l);
        }
        unset($l);
        arsort($out['counts']);

        $out['prevCounts'] = DB::table('analytics_events')
            ->whereBetween('created_at', [$this->prevStart->utc(), $this->start->utc()])
            ->selectRaw('name, COUNT(*) as c')->groupBy('name')->pluck('c', 'name')->map(fn ($n) => (int) $n)->all();

        return $out;
    }

    private function previousWebTotals(): array
    {
        $visitors = [];
        $sessions = [];
        $visits = 0;
        foreach ($this->visitRows($this->prevStart, $this->start) as $row) {
            $visits++;
            $day = CarbonImmutable::parse($row->visited_at, 'UTC')->setTimezone(self::TZ)->toDateString();
            $visitors[$day.'|'.$this->visitorKey($row)] = true;
            $sessions[$row->session_id ?: 'v'.$row->id] = true;
        }

        return ['visits' => $visits, 'visitors' => count($visitors), 'sessions' => count($sessions)];
    }

    private function live(): int
    {
        return DB::table('page_visits')->where('visited_at', '>=', now()->subMinutes(30))
            ->get(['id', 'visitor_hash', 'ip'])->map(fn ($r) => $this->visitorKey($r))->unique()->count();
    }

    private function visitRows(CarbonImmutable $from, ?CarbonImmutable $to = null)
    {
        return DB::table('page_visits')
            ->where('visited_at', '>=', $from->utc())
            ->when($to, fn ($q) => $q->where('visited_at', '<', $to->utc()))
            ->select(['id', 'path', 'visited_at', 'visitor_hash', 'ip', 'session_id', 'referrer_host', 'device', 'os', 'browser', 'lang', 'country_code', 'utm_source', 'utm_campaign'])
            ->cursor();
    }

    // Rows recorded before visitor hashes existed fall back to their IP, then to the row itself.
    private function visitorKey(object $row): string
    {
        return $row->visitor_hash ?: ($row->ip ?: 'row'.$row->id);
    }

    private function bump(?array &$list, ?string $key): void
    {
        if ($key === null || $key === '') {
            return;
        }
        $list ??= [];
        $list[$key] = ($list[$key] ?? 0) + 1;
    }
}
