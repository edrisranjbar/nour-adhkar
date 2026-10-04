<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\StoreStats\BazaarReviews;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(BazaarReviews $reviews)
    {
        $weekAgo = now()->subWeek();

        return view('panel.dashboard', [
            'stats' => [
                'users' => User::count(),
                'verified' => User::whereNotNull('email_verified_at')->count(),
                'usersWeek' => User::where('created_at', '>=', $weekAgo)->count(),
                'feedback' => DB::table('app_feedback')->count(),
                'notices' => DB::table('app_notices')->where('published', true)->count(),
                'reads' => DB::table('app_notice_reads')->count(),
            ],
            'latestFeedback' => DB::table('app_feedback as f')->leftJoin('users as u', 'u.id', '=', 'f.user_id')
                ->select('f.*', 'u.name as user_name', 'u.email as user_email')
                ->orderByDesc('f.created_at')->limit(5)->get(),
            'bazaarStatus' => $reviews->current(),
            'bazaarItems' => $reviews->query()->limit(5)->get(),
            'recentUsers' => User::orderByDesc('created_at')->limit(6)->get(['name', 'email_verified_at', 'created_at']),
            'ai' => $this->lectureAi(),
        ]);
    }

    /** Description generation from lecture audio: overall progress and the lectures being worked on. */
    private function lectureAi(): array
    {
        $withAudio = DB::table('lectures')->where(fn ($q) => $q->whereNotNull('audio_path')->orWhereNotNull('audio_url'));
        $counts = (clone $withAudio)->selectRaw('ai_status, count(*) as n')->groupBy('ai_status')->pluck('n', 'ai_status');
        $total = (int) $counts->sum();
        $done = (int) (clone $withAudio)->whereNotNull('ai_processed_at')->count();

        return [
            'total' => $total,
            'done' => $done,
            'queued' => (int) ($counts['queued'] ?? 0),
            'processing' => (int) ($counts['processing'] ?? 0),
            'failed' => (int) ($counts['failed'] ?? 0),
            'active' => DB::table('lectures as l')->join('scholars as s', 's.id', '=', 'l.scholar_id')
                ->whereIn('l.ai_status', ['queued', 'processing', 'failed'])
                ->orderByRaw("case l.ai_status when 'processing' then 0 when 'queued' then 1 else 2 end")
                ->orderByDesc('l.updated_at')->limit(8)
                ->get(['l.id', 'l.title', 'l.scholar_id', 's.name as scholar', 'l.ai_status', 'l.ai_error', 'l.ai_claimed_at', 'l.updated_at']),
        ];
    }
}
