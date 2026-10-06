<?php

namespace App\Http\Controllers;

use App\Services\InstagramPublishing;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InstagramScheduleController extends Controller
{
    public function store(Request $request, int $id)
    {
        abort_unless(InstagramPublishing::configured(), 422, 'Instagram account is not connected.');
        $data = $request->validate([
            'scheduled_local' => 'required|date_format:Y-m-d\TH:i',
            'render_token' => 'required|string|size:64',
            'image' => 'required|file|mimes:jpg,jpeg|max:2048|dimensions:width=1080,height=1350',
        ]);
        $time = CarbonImmutable::createFromFormat('!Y-m-d\TH:i', $data['scheduled_local'], 'Asia/Tehran')->utc();
        abort_unless($time->isFuture() && $time->lessThan(now()->addYear()), 422, 'Choose a future time within one year.');
        return DB::transaction(function () use ($id, $data, $time, $request) {
            $post = DB::table('instagram_posts')->where('id', $id)->lockForUpdate()->first();
            abort_unless($post, 404);
            abort_unless($post->status === 'queued', 422, 'Review and queue this post first.');
            abort_unless(hash_equals(InstagramPublishing::renderToken($post), $data['render_token']), 409, 'Post changed. Reload and render again.');
            $path = Storage::disk('public')->putFileAs('instagram-posts', $request->file('image'), Str::uuid().'.jpg');
            abort_unless($path, 500, 'Could not save image.');
            DB::table('instagram_posts')->where('id', $id)->update([
                'status' => 'scheduled', 'scheduled_at' => $time, 'image_path' => $path,
                'instagram_account_id' => config('services.instagram.account_id'), 'instagram_container_id' => null, 'publish_started_at' => null,
                'publish_error' => null, 'publish_claim' => null, 'claimed_at' => null, 'updated_at' => now(),
            ]);
            return response()->json(['message' => 'Scheduled', 'scheduled_at' => $time->toIso8601String()]);
        });
    }

    public function reconcile(Request $request, int $id)
    {
        $data = $request->validate(['instagram_url' => ['required', 'string', 'max:500', 'regex:~^https://(?:www\.)?instagram\.com/(?:p|reel)/[A-Za-z0-9_-]+/?$~']]);
        return DB::transaction(function () use ($id, $data) {
            $post = DB::table('instagram_posts')->where('id', $id)->lockForUpdate()->first();
            abort_unless($post, 404);
            abort_unless($post->status === 'publishing' && $post->publish_started_at && $post->publish_error, 422);
            DB::table('instagram_posts')->where('id', $id)->update(['status' => 'published', 'instagram_url' => $data['instagram_url'], 'published_at' => now(), 'publish_error' => null, 'updated_at' => now()]);
            return response()->noContent();
        });
    }

    public function destroy(int $id)
    {
        return DB::transaction(function () use ($id) {
            $post = DB::table('instagram_posts')->where('id', $id)->lockForUpdate()->first();
            abort_unless($post, 404);
            abort_unless(in_array($post->status, ['scheduled', 'failed']) && !$post->publish_started_at, 422, 'Publishing has started; cancellation is unavailable.');
            DB::table('instagram_posts')->where('id', $id)->update(['status' => 'queued', 'scheduled_at' => null, 'image_path' => null, 'publish_error' => null, 'publish_claim' => null, 'claimed_at' => null, 'updated_at' => now()]);
            if ($post->image_path) Storage::disk('public')->delete($post->image_path);
            return response()->noContent();
        });
    }
}
