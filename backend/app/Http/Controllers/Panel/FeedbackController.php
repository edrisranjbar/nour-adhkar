<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\StoreStats\BazaarReviews;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FeedbackController extends Controller
{
    public const TYPES = ['suggestion' => 'پیشنهاد', 'criticism' => 'انتقاد', 'other' => 'سایر'];

    public function index(Request $request, BazaarReviews $reviews)
    {
        $source = in_array($request->query('source'), ['app', 'bazaar'], true) ? $request->query('source') : 'all';
        $bazaarStatus = $source !== 'app' ? $reviews->current() : null;
        $bazaarItems = $source !== 'app' ? $reviews->query()->paginate(30, ['*'], 'bazaar_page')->withQueryString() : null;
        $type = $request->query('type');
        $items = DB::table('app_feedback as f')
            ->leftJoin('users as u', 'u.id', '=', 'f.user_id')
            ->select('f.*', 'u.name as user_name', 'u.email as user_email')
            ->when(array_key_exists((string) $type, self::TYPES), fn ($q) => $q->where('f.type', $type))
            ->orderByDesc('f.created_at')
            ->paginate(30)
            ->withQueryString();

        return view('panel.feedback.index', ['items' => $items, 'type' => $type, 'types' => self::TYPES,
            'source' => $source, 'bazaarStatus' => $bazaarStatus, 'bazaarItems' => $bazaarItems]);
    }

    /** «پسندیدن»: toggles the like the sender sees in the app. */
    public function like(int $id)
    {
        $item = DB::table('app_feedback')->find($id);
        abort_unless($item, 404);
        DB::table('app_feedback')->where('id', $id)->update(['liked_at' => $item->liked_at ? null : now(), 'updated_at' => now()]);
        return back()->with('status', $item->liked_at ? 'پسند برداشته شد.' : 'پیام پسندیده شد.');
    }

    /** Saves (or, when empty, removes) the team's reply shown to the sender in «پیام‌های من». */
    public function reply(Request $request, int $id)
    {
        abort_unless(DB::table('app_feedback')->where('id', $id)->exists(), 404);
        $data = $request->validate(['reply' => 'nullable|string|max:3000'], [], ['reply' => 'پاسخ']);
        $reply = trim((string) ($data['reply'] ?? ''));
        DB::table('app_feedback')->where('id', $id)->update([
            'reply' => $reply === '' ? null : $reply,
            'replied_at' => $reply === '' ? null : now(),
            'updated_at' => now(),
        ]);
        return back()->with('status', $reply === '' ? 'پاسخ حذف شد.' : 'پاسخ ثبت شد؛ فرستنده آن را در برنامه می‌بیند.');
    }

    public function destroy(int $id)
    {
        DB::table('app_feedback')->where('id', $id)->delete();

        return back()->with('status', 'بازخورد حذف شد.');
    }
}
