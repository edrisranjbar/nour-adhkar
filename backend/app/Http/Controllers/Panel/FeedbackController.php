<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FeedbackController extends Controller
{
    public const TYPES = ['suggestion' => 'پیشنهاد', 'criticism' => 'انتقاد', 'other' => 'سایر'];

    public function index(Request $request)
    {
        $type = $request->query('type');
        $items = DB::table('app_feedback')
            ->when(array_key_exists((string) $type, self::TYPES), fn ($q) => $q->where('type', $type))
            ->orderByDesc('created_at')
            ->paginate(30)
            ->withQueryString();

        return view('panel.feedback.index', ['items' => $items, 'type' => $type, 'types' => self::TYPES]);
    }

    public function destroy(int $id)
    {
        DB::table('app_feedback')->where('id', $id)->delete();
        return back()->with('status', 'بازخورد حذف شد.');
    }
}
