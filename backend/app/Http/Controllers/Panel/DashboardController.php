<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $weekAgo = now()->subWeek();

        return view('panel.dashboard', [
            'stats' => [
                'users' => User::count(),
                'verified' => User::whereNotNull('email_verified_at')->count(),
                'usersWeek' => User::where('created_at', '>=', $weekAgo)->count(),
                'feedback' => DB::table('app_feedback')->count(),
                'feedbackWeek' => DB::table('app_feedback')->where('created_at', '>=', $weekAgo)->count(),
                'notices' => DB::table('app_notices')->where('published', true)->count(),
                'reads' => DB::table('app_notice_reads')->count(),
            ],
            'latestFeedback' => DB::table('app_feedback')->orderByDesc('created_at')->limit(5)->get(),
        ]);
    }
}
