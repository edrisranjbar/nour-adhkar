<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * GET /api/app-adhkar: published adhkar from the admin panel («اذکار برنامه»), grouped by collection
 * in panel order. The app merges them with its built-in adhkar and refreshes in the background; the
 * ETag changes on any add, edit, reorder, toggle or delete, so an unchanged list is a tiny 304.
 */
class AppAdhkarController extends Controller
{
    public function index(Request $request)
    {
        $c = DB::table('app_dhikr_collections')->selectRaw('count(*) as n, max(id) as i, max(updated_at) as u, sum(sort_order) as s')->first();
        $d = DB::table('app_dhikrs')->selectRaw('count(*) as n, max(id) as i, max(updated_at) as u, sum(sort_order) as s')->first();
        $etag = '"' . sha1(implode('|', [$c->n, $c->i, $c->u, $c->s, $d->n, $d->i, $d->u, $d->s])) . '"';
        if (trim((string) $request->header('If-None-Match')) === $etag) {
            return response('', 304)->header('ETag', $etag);
        }

        $items = DB::table('app_dhikrs')->where('published', true)
            ->orderBy('sort_order')->orderBy('id')
            ->get(['id', 'collection_id', 'arabic_text', 'translation', 'source', 'target_count'])
            ->groupBy('collection_id');

        $collections = DB::table('app_dhikr_collections')->where('published', true)
            ->orderBy('sort_order')->orderBy('id')->get()
            ->map(fn ($col) => [
                'id' => (int) $col->id,
                'key' => $col->builtin_key,
                'title' => $col->builtin_key ? null : $col->title,
                'description' => $col->builtin_key ? null : $col->description,
                'icon' => $col->icon,
                'items' => ($items[$col->id] ?? collect())->map(fn ($d) => [
                    'id' => (int) $d->id,
                    'arabic' => $d->arabic_text,
                    'translation' => $d->translation,
                    'source' => $d->source,
                    'count' => (int) $d->target_count,
                ])->values(),
            ])
            ->filter(fn ($col) => count($col['items']) > 0)
            ->values();

        return response()->json(['data' => $collections])->header('ETag', $etag);
    }
}
