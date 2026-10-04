<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppInboxController extends Controller
{
    public function sendFeedback(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:suggestion,criticism,other',
            'message' => 'required|string|min:3|max:3000',
        ]);
        // Signed-in app users send their JWT so the panel can show who wrote. Older app versions send no
        // token and stay anonymous; a token that is present but invalid or expired asks the app to sign in again.
        $user = null;
        if ($request->bearerToken()) {
            $user = rescue(fn () => auth('api')->user(), null, false);
            if (!$user) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }
        }
        DB::table('app_feedback')->insert($data + ['user_id' => $user?->id, 'created_at' => now(), 'updated_at' => now()]);
        return response()->json(['message' => 'Feedback received'], 201);
    }

    public function notices(Request $request)
    {
        $data = $request->validate(['installation_id' => 'required|uuid']);
        $items = DB::table('app_notices as n')
            ->leftJoin('app_notice_reads as r', function ($join) use ($data) {
                $join->on('r.notice_id', '=', 'n.id')->where('r.installation_id', '=', $data['installation_id']);
            })
            ->where('n.published', true)
            ->orderByDesc('n.created_at')
            ->limit(100)
            ->get(['n.id', 'n.title', 'n.message', 'n.created_at', 'r.read_at']);
        return response()->json(['data' => $items]);
    }

    public function markRead(Request $request, int $id)
    {
        $data = $request->validate(['installation_id' => 'required|uuid']);
        abort_unless(DB::table('app_notices')->where('id', $id)->where('published', true)->exists(), 404);
        DB::table('app_notice_reads')->insertOrIgnore([
            'notice_id' => $id,
            'installation_id' => $data['installation_id'],
            'read_at' => now(),
        ]);
        return response()->json(['message' => 'Read']);
    }

    public function adminFeedback()
    {
        return response()->json(DB::table('app_feedback')->orderByDesc('created_at')->paginate(30));
    }

    public function adminNotices()
    {
        return response()->json(DB::table('app_notices')->orderByDesc('created_at')->paginate(30));
    }

    public function createNotice(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:160',
            'message' => 'required|string|max:5000',
            'published' => 'required|boolean',
        ]);
        $id = DB::table('app_notices')->insertGetId($data + ['created_at' => now(), 'updated_at' => now()]);
        return response()->json(['id' => $id], 201);
    }

    public function updateNotice(Request $request, int $id)
    {
        $data = $request->validate([
            'title' => 'sometimes|required|string|max:160',
            'message' => 'sometimes|required|string|max:5000',
            'published' => 'sometimes|required|boolean',
        ]);
        abort_unless(DB::table('app_notices')->where('id', $id)->exists(), 404);
        DB::table('app_notices')->where('id', $id)->update($data + ['updated_at' => now()]);
        return response()->json(['message' => 'Updated']);
    }
}
