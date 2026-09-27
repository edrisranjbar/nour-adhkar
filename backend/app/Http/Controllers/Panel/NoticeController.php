<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NoticeController extends Controller
{
    public function index()
    {
        $items = DB::table('app_notices as n')
            ->select('n.*')
            ->selectSub(fn ($q) => $q->from('app_notice_reads as r')->selectRaw('count(*)')->whereColumn('r.notice_id', 'n.id'), 'reads_count')
            ->orderByDesc('n.created_at')
            ->paginate(30);

        return view('panel.notices.index', compact('items'));
    }

    public function create()
    {
        return view('panel.notices.form', ['notice' => null]);
    }

    public function store(Request $request)
    {
        DB::table('app_notices')->insert($this->validated($request) + ['created_at' => now(), 'updated_at' => now()]);
        return redirect()->route('panel.notices.index')->with('status', 'پیام ذخیره شد.');
    }

    public function edit(int $id)
    {
        $notice = DB::table('app_notices')->find($id);
        abort_unless($notice, 404);
        return view('panel.notices.form', compact('notice'));
    }

    public function update(Request $request, int $id)
    {
        abort_unless(DB::table('app_notices')->where('id', $id)->exists(), 404);
        DB::table('app_notices')->where('id', $id)->update($this->validated($request) + ['updated_at' => now()]);
        return redirect()->route('panel.notices.index')->with('status', 'پیام به‌روزرسانی شد.');
    }

    public function toggle(int $id)
    {
        $notice = DB::table('app_notices')->find($id);
        abort_unless($notice, 404);
        DB::table('app_notices')->where('id', $id)->update(['published' => !$notice->published, 'updated_at' => now()]);
        return back()->with('status', $notice->published ? 'پیام به پیش‌نویس برگشت.' : 'پیام منتشر شد.');
    }

    public function destroy(int $id)
    {
        DB::table('app_notices')->where('id', $id)->delete();
        return back()->with('status', 'پیام حذف شد.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:160',
            'message' => 'required|string|max:5000',
        ]);
        return $data + ['published' => $request->boolean('published')];
    }
}
