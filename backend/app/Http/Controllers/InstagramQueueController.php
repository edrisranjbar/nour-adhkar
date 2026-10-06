<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Services\InstagramPublishing;

class InstagramQueueController extends Controller
{
    private function catalog(): array
    {
        return json_decode(file_get_contents(resource_path('data/instagram-verses.json')), true, 512, JSON_THROW_ON_ERROR);
    }

    public function index()
    {
        $templates = DB::table('instagram_templates')->orderBy('id')->get()->map(function ($template) {
            $template->design = json_decode($template->design, true);
            $template->is_default = (bool) $template->is_default;
            return $template;
        });
        return response()->json(['catalog' => $this->catalog(), 'templates' => $templates, 'publishing' => ['configured' => InstagramPublishing::configured(), 'username' => config('services.instagram.username')], 'data' => DB::table('instagram_posts')->orderBy('position')->orderBy('id')->get()->map(fn ($post) => $this->decodePost($post))]);
    }

    private function decodePost(object $post): object
    {
        $post->render_token = InstagramPublishing::renderToken($post);
        $post->design = $post->design ? json_decode($post->design, true) : null;
        return $post;
    }

    public function store(Request $request)
    {
        $catalog = collect($this->catalog())->keyBy('key');
        $data = $request->validate(['verse_key' => ['required', Rule::in($catalog->keys()->all())]]);
        $verse = $catalog[$data['verse_key']];
        $template = DB::table('instagram_templates')->where('is_default', true)->first();
        // Duplicate clicks and simultaneous requests must not create duplicate posts.
        $created = DB::table('instagram_posts')->insertOrIgnore([
            'verse_key' => $verse['key'], 'caption' => $verse['translation']."\n\n".$verse['reference']."\n".$verse['translator']."\n".$verse['source_url']."\n\nاذکار نور · adhkar.ir\n#قرآن #اذکار_نور",
            'image_text' => $verse['translation'], 'translator_key' => 'khorramdel',
            'theme' => 'paper', 'status' => 'draft', 'position' => 0,
            'template_id' => $template?->id, 'design' => $template?->design,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        return response()->json(['data' => $this->decodePost(DB::table('instagram_posts')->where('verse_key', $verse['key'])->first())], $created ? 201 : 200);
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'caption' => 'sometimes|required|string|max:2200',
            'image_text' => 'sometimes|required|string|max:1200',
            'translator_key' => 'sometimes|required|in:rowwad,khorramdel',
            'theme' => 'sometimes|required|in:paper,white,night',
            'template_id' => 'sometimes|required|integer|exists:instagram_templates,id',
            'status' => 'sometimes|required|in:draft,queued,published',
            'instagram_url' => ['nullable', 'string', 'max:500', 'regex:~^https://(?:www\.)?instagram\.com/(?:p|reel)/[A-Za-z0-9_-]+/?$~'],
        ]);
        return DB::transaction(function () use ($id, $data) {
            $post = DB::table('instagram_posts')->where('id', $id)->lockForUpdate()->first();
            abort_unless($post, 404);
            abort_if(!in_array($post->status, ['draft', 'queued']), 422, 'Cancel the schedule before editing this post.');
            if (isset($data['template_id'])) {
                $template = DB::table('instagram_templates')->where('id', $data['template_id'])->lockForUpdate()->first();
                abort_unless($template, 422, 'Template no longer exists.');
                $data['design'] = $template->design;
            } elseif (isset($data['theme'])) {
                // Preserve compatibility with the previous three-theme editor.
                $name = ['paper' => 'کاغذی', 'white' => 'سفید', 'night' => 'شب'][$data['theme']];
                $template = DB::table('instagram_templates')->where('name', $name)->first();
                if ($template) { $data['template_id'] = $template->id; $data['design'] = $template->design; }
            }
            if (($data['status'] ?? null) === 'published') {
                abort_unless($post->status === 'queued' && !empty($data['instagram_url']), 422, 'Review the post and provide its Instagram permalink first.');
                $data['published_at'] = now();
            }
            if (($data['status'] ?? null) === 'queued' && $post->status !== 'queued') {
                $data['position'] = (int) DB::table('instagram_posts')->max('position') + 1;
            }
            DB::table('instagram_posts')->where('id', $id)->update($data + ['updated_at' => now()]);
            return response()->json(['data' => $this->decodePost(DB::table('instagram_posts')->where('id', $id)->first())]);
        });
    }

    public function reorder(Request $request)
    {
        $data = $request->validate(['ids' => 'required|array|min:1', 'ids.*' => 'required|integer|distinct']);
        return DB::transaction(function () use ($data) {
            $current = DB::table('instagram_posts')->where('status', 'queued')->orderBy('id')->lockForUpdate()->pluck('id')->map(fn ($id) => (int) $id)->all();
            $incoming = array_map('intval', $data['ids']);
            sort($incoming);
            abort_unless($incoming === $current, 409, 'Queue changed. Reload before reordering.');
            foreach ($data['ids'] as $position => $id) {
                DB::table('instagram_posts')->where('id', $id)->update(['position' => $position + 1, 'updated_at' => now()]);
            }
            return response()->json(['message' => 'Queue reordered']);
        });
    }

    public function destroy(int $id)
    {
        return DB::transaction(function () use ($id) {
            $post = DB::table('instagram_posts')->where('id', $id)->lockForUpdate()->first();
            abort_unless($post, 404);
            abort_if(!in_array($post->status, ['draft', 'queued']), 422, 'Cancel the schedule before deleting this post.');
            DB::table('instagram_posts')->where('id', $id)->delete();
            return response()->noContent();
        });
    }
}
