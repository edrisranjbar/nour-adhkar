<?php

namespace App\Http\Controllers;

use App\Services\InstagramPublishing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InstagramWorkerController extends Controller
{
    private function authorizeWorker(Request $request): void
    {
        $token = (string) config('services.instagram.worker_token');
        abort_unless($token && hash_equals($token, (string) $request->bearerToken()), 401);
    }

    public function claim(Request $request)
    {
        $this->authorizeWorker($request);
        $data = $request->validate(['account_id' => 'required|string|max:100']);
        abort_unless(InstagramPublishing::configured() && hash_equals((string) config('services.instagram.account_id'), $data['account_id']), 422, 'Publishing account mismatch.');
        return DB::transaction(function () use ($data) {
            // A claimed post is never automatically reclaimed: an interrupted publish can be ambiguous.
            $post = DB::table('instagram_posts')->where('status', 'scheduled')->where('instagram_account_id', $data['account_id'])
                ->where('scheduled_at', '<=', now())->orderBy('scheduled_at')->lockForUpdate()->first();
            if (!$post) return response()->json(['data' => null]);
            $claim = (string) Str::uuid();
            DB::table('instagram_posts')->where('id', $post->id)->update(['status' => 'publishing', 'publish_claim' => $claim, 'claimed_at' => now(), 'updated_at' => now()]);
            return response()->json(['data' => ['id' => $post->id, 'claim' => $claim, 'image_url' => asset('storage/'.$post->image_path), 'caption' => $post->caption]]);
        });
    }

    public function report(Request $request, int $id)
    {
        $this->authorizeWorker($request);
        $data = $request->validate([
            'claim' => 'required|uuid', 'stage' => 'required|in:container,publish_started,published,failed',
            'container_id' => 'required_if:stage,container|nullable|regex:/^\d+$/|max:100',
            'media_id' => 'required_if:stage,published|nullable|regex:/^\d+$/|max:100',
            'permalink' => ['required_if:stage,published', 'nullable', 'string', 'max:500', 'regex:~^https://(?:www\.)?instagram\.com/(?:p|reel)/[A-Za-z0-9_-]+/?$~'],
            'error' => 'required_if:stage,failed|nullable|string|max:500',
        ]);
        return DB::transaction(function () use ($id, $data) {
            $post = DB::table('instagram_posts')->where('id', $id)->lockForUpdate()->first();
            abort_unless($post && $post->publish_claim && hash_equals($post->publish_claim, $data['claim']), 409);
            if ($post->status === 'published' && $data['stage'] === 'published' && $post->instagram_media_id === $data['media_id']) return response()->json(['message' => 'Already recorded']);
            abort_unless($post->status === 'publishing', 409);
            $update = ['updated_at' => now()];
            if ($data['stage'] === 'container') {
                abort_if($post->publish_started_at || ($post->instagram_container_id && $post->instagram_container_id !== $data['container_id']), 409);
                $update['instagram_container_id'] = $data['container_id'];
            } elseif ($data['stage'] === 'publish_started') {
                abort_unless($post->instagram_container_id && !$post->publish_started_at, 409);
                $update['publish_started_at'] = now();
            } elseif ($data['stage'] === 'published') {
                abort_unless($post->publish_started_at, 409);
                $update += ['status' => 'published', 'instagram_media_id' => $data['media_id'], 'instagram_url' => $data['permalink'], 'published_at' => now(), 'publish_error' => null];
            } else {
                // Failures after the publish call remain locked for manual reconciliation, avoiding duplicates.
                $update += ['status' => $post->publish_started_at ? 'publishing' : 'failed', 'publish_error' => $data['error']];
            }
            DB::table('instagram_posts')->where('id', $id)->update($update);
            return response()->json(['message' => 'Recorded']);
        });
    }
}
