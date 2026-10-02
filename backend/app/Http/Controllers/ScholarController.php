<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

/**
 * GET /api/scholars for the app's «علما و مشاهیر»: published scholars with their published
 * lectures, both in display order. Shape matches the app's ScholarsRepository.
 */
class ScholarController extends Controller
{
    public function index()
    {
        $scholars = DB::table('scholars')->where('published', true)->orderBy('sort_order')->orderBy('id')->get();
        $lectures = DB::table('lectures')
            ->whereIn('scholar_id', $scholars->pluck('id'))
            ->where('published', true)
            ->orderBy('sort_order')->orderBy('id')
            ->get()
            ->groupBy('scholar_id');

        $data = $scholars->map(fn ($s) => [
            'id' => $s->slug,
            'name' => $s->name,
            'tagline' => (string) $s->tagline,
            'bio' => (string) $s->bio,
            'hue' => (int) $s->hue,
            // Absolute URL of the uploaded photo, or null (the app then draws generated cover art).
            'photoUrl' => $s->photo_path ? asset('storage/' . ltrim($s->photo_path, '/')) : null,
            'lectures' => ($lectures[$s->id] ?? collect())
                ->map(fn ($l) => array_filter([
                    'id' => (string) $l->id,
                    'title' => $l->title,
                    'description' => (string) $l->description,
                    'audioUrl' => self::audioUrl($l),
                    'durationSec' => $l->duration_sec ? (int) $l->duration_sec : null,
                    // Shown only after an admin reviewed the generated text (see LectureAiController).
                    'summary' => $l->text_published && $l->summary ? $l->summary : null,
                    // The full transcript is large, so the app loads it on demand from GET /api/lectures/{id}/transcript.
                    'hasTranscript' => $l->text_published && $l->transcript ? true : null,
                ], fn ($v) => $v !== null))
                ->filter(fn ($l) => !empty($l['audioUrl']))
                ->values(),
        ])->values();

        return response()->json(['data' => $data]);
    }

    /** GET /api/lectures/{id}/transcript: the reviewed transcript of a published lecture. */
    public function transcript(int $id)
    {
        $lecture = DB::table('lectures as l')
            ->join('scholars as s', 's.id', '=', 'l.scholar_id')
            ->where('l.id', $id)->where('l.published', true)->where('s.published', true)
            ->where('l.text_published', true)->whereNotNull('l.transcript')
            ->first(['l.transcript']);
        abort_unless($lecture, 404);
        return response()->json(['data' => ['transcript' => $lecture->transcript]]);
    }

    /**
     * Absolute URL of a lecture's audio: the uploaded file, otherwise the external link.
     * Built from the request host (asset()) rather than APP_URL, so a wrong or missing APP_URL
     * can never hand the app a relative or localhost link. Needs `php artisan storage:link`.
     */
    public static function audioUrl(object $lecture): ?string
    {
        if ($lecture->audio_path) {
            return asset('storage/' . ltrim($lecture->audio_path, '/'));
        }
        return $lecture->audio_url ?: null;
    }
}
