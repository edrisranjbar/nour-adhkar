<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Work queue for the lecture-ai GitHub workflow, which transcribes and summarizes lectures outside
 * Iran (this server cannot reach Groq). Protected by LECTURE_AI_TOKEN. The workflow claims
 * lectures an admin queued from the edit form, downloads their audio, and posts the transcript and
 * summary back here; the result is written into the lecture description.
 */
class LectureAiController extends Controller
{
    /** A claim older than this is treated as abandoned (runner timeout or crash) and offered again. */
    private const CLAIM_MINUTES = 120;

    public function claim(Request $request)
    {
        if ($denied = $this->deny($request)) {
            return $denied;
        }
        $limit = max(1, min(5, (int) $request->query('limit', 1)));

        $lectures = DB::transaction(function () use ($limit) {
            $rows = DB::table('lectures')
                ->where(function ($q) {
                    $q->where('ai_status', 'queued')
                        ->orWhere(fn ($q) => $q->where('ai_status', 'processing')
                            ->where('ai_claimed_at', '<', now()->subMinutes(self::CLAIM_MINUTES)));
                })
                ->where(fn ($q) => $q->whereNotNull('audio_path')->orWhereNotNull('audio_url'))
                ->orderBy('id')
                ->limit($limit)
                ->lockForUpdate()
                ->get();
            DB::table('lectures')->whereIn('id', $rows->pluck('id'))
                ->update(['ai_status' => 'processing', 'ai_claimed_at' => now(), 'ai_error' => null]);
            return $rows;
        });

        return response()->json(['data' => $lectures->map(fn ($l) => [
            'id' => (int) $l->id,
            'title' => $l->title,
            'audioUrl' => ScholarController::audioUrl($l),
            'durationSec' => $l->duration_sec ? (int) $l->duration_sec : null,
        ])->values()]);
    }

    public function report(Request $request, int $id)
    {
        if ($denied = $this->deny($request)) {
            return $denied;
        }
        $data = $request->validate([
            'transcript' => 'nullable|string|max:1000000',
            'summary' => 'nullable|string|max:20000',
            'error' => 'nullable|string|max:2000',
            // true when the run hit a rate limit and the lecture should simply be tried again later.
            'retry' => 'nullable|boolean',
        ]);
        $lecture = DB::table('lectures')->find($id);
        abort_unless($lecture, 404);

        if (!empty($data['error']) || empty($data['transcript'])) {
            DB::table('lectures')->where('id', $id)->update([
                'ai_status' => $request->boolean('retry') ? 'queued' : 'failed',
                'ai_error' => mb_substr($data['error'] ?? 'Empty transcript', 0, 500),
                'ai_claimed_at' => null,
                'updated_at' => now(),
            ]);
            return response()->json(['success' => true]);
        }

        // The admin asked for this from the edit form, so the result replaces the description.
        $transcript = trim($data['transcript']);
        $summary = trim((string) ($data['summary'] ?? ''));
        DB::table('lectures')->where('id', $id)->update([
            'description' => $summary === '' ? $transcript : "خلاصه\n{$summary}\n\nمتن کامل سخنرانی\n{$transcript}",
            'transcript' => $transcript,
            'summary' => $summary === '' ? null : $summary,
            'ai_status' => 'done', 'ai_error' => null, 'ai_claimed_at' => null,
            'ai_processed_at' => now(), 'updated_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    private function deny(Request $request)
    {
        $expected = (string) config('services.lecture_ai.token');
        if ($expected === '' || !hash_equals($expected, (string) $request->bearerToken())) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        return null;
    }
}
