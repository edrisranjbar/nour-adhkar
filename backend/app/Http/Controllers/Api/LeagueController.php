<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LeagueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class LeagueController extends Controller
{
    public function __construct(
        private readonly LeagueService $leagueService
    ) {}

    public function getProgress(): JsonResponse
    {
        try {
            $user = auth()->user();
            
            if (!$user) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            $progress = $this->leagueService->getProgressToNextLeague($user);

            return response()->json($progress);
        } catch (\Exception $e) {
            Log::error('Error in LeagueController@getProgress: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while fetching league progress'], 500);
        }
    }
} 