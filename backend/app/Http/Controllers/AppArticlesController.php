<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * GET /api/app-articles: every published article for the app's «مقالات», newest first, in one lean
 * response. The ETag changes whenever an article is added, edited, published or removed, so the
 * app's background refresh usually gets a tiny 304 and keeps its cache.
 */
class AppArticlesController extends Controller
{
    public function index(Request $request)
    {
        $published = DB::table('posts')
            ->where('status', 'published')->whereNotNull('published_at')->where('published_at', '<=', now());

        // The app's article collection defaults to Persian and only includes translations
        // when a client explicitly requests that language.
        $locale = $request->query('locale', 'fa');
        if (!in_array($locale, ['fa', 'ar', 'ckb'], true)) {
            return response()->json(['message' => 'Unsupported article language.'], 422);
        }
        $published->where(function ($query) use ($locale) {
            $query->where('locale', $locale);
            // Older integrations and direct database fixtures may still create
            // unlabelled records; treat those as Persian until they are tagged.
            if ($locale === 'fa') $query->orWhereNull('locale');
        });

        $stamp = (clone $published)->selectRaw('count(*) as n, max(updated_at) as u, max(published_at) as p')->first();
        $etag = '"' . sha1(($stamp->n ?? 0) . '|' . ($stamp->u ?? '') . '|' . ($stamp->p ?? '')) . '"';
        if (trim((string) $request->header('If-None-Match')) === $etag) {
            return response('', 304)->header('ETag', $etag);
        }

        $articles = (clone $published)
            ->orderByDesc('published_at')->orderByDesc('id')
            ->get(['id', 'slug', 'title', 'excerpt', 'content', 'published_at'])
            ->map(fn ($a) => [
                'id' => (int) $a->id,
                'slug' => $a->slug,
                'title' => $a->title,
                'excerpt' => $a->excerpt,
                'content' => $a->content,
                'publishedAt' => $a->published_at,
            ]);

        return response()->json(['data' => $articles])->header('ETag', $etag);
    }
}
