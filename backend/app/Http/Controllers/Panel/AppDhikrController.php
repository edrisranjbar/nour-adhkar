<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Admin panel: the adhkar inside one «اذکار برنامه» collection. */
class AppDhikrController extends Controller
{
    public function index(int $collectionId)
    {
        $collection = $this->collection($collectionId);
        $items = DB::table('app_dhikrs')->where('collection_id', $collectionId)->orderBy('sort_order')->orderBy('id')->get();
        return view('panel.app-dhikrs.index', compact('collection', 'items'));
    }

    public function create(int $collectionId)
    {
        return view('panel.app-dhikrs.form', ['collection' => $this->collection($collectionId), 'dhikr' => null]);
    }

    public function store(Request $request, int $collectionId)
    {
        $this->collection($collectionId);
        $next = (int) DB::table('app_dhikrs')->where('collection_id', $collectionId)->max('sort_order') + 10;
        DB::table('app_dhikrs')->insert($this->validated($request) + [
            'collection_id' => $collectionId, 'sort_order' => $next, 'created_at' => now(), 'updated_at' => now(),
        ]);
        return redirect()->route('panel.app-dhikrs.index', $collectionId)->with('status', 'ذکر اضافه شد.');
    }

    public function edit(int $collectionId, int $id)
    {
        return view('panel.app-dhikrs.form', ['collection' => $this->collection($collectionId), 'dhikr' => $this->dhikr($collectionId, $id)]);
    }

    public function update(Request $request, int $collectionId, int $id)
    {
        $this->dhikr($collectionId, $id);
        DB::table('app_dhikrs')->where('id', $id)->update($this->validated($request) + ['updated_at' => now()]);
        return redirect()->route('panel.app-dhikrs.index', $collectionId)->with('status', 'ذکر به‌روزرسانی شد.');
    }

    public function toggle(int $collectionId, int $id)
    {
        $dhikr = $this->dhikr($collectionId, $id);
        DB::table('app_dhikrs')->where('id', $id)->update(['published' => !$dhikr->published, 'updated_at' => now()]);
        return back()->with('status', $dhikr->published ? 'ذکر از برنامه پنهان شد.' : 'ذکر در برنامه نمایش داده می‌شود.');
    }

    public function move(int $collectionId, int $id, string $direction)
    {
        $this->dhikr($collectionId, $id);
        Reorder::move('app_dhikrs', $id, $direction, 'collection_id');
        DB::table('app_dhikrs')->where('id', $id)->update(['updated_at' => now()]);
        return back();
    }

    public function destroy(int $collectionId, int $id)
    {
        $this->dhikr($collectionId, $id);
        DB::table('app_dhikrs')->where('id', $id)->delete();
        return back()->with('status', 'ذکر حذف شد.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'arabic_text' => 'required|string|max:10000',
            'translation' => 'nullable|string|max:10000',
            'source' => 'nullable|string|max:500',
            'target_count' => 'required|integer|min:1|max:10000',
        ], [], [
            'arabic_text' => 'متن عربی', 'translation' => 'ترجمه فارسی', 'source' => 'منبع', 'target_count' => 'تعداد تکرار',
        ]);
        return $data + ['published' => $request->boolean('published')];
    }

    private function collection(int $id): object
    {
        $collection = DB::table('app_dhikr_collections')->find($id);
        abort_unless($collection, 404);
        return $collection;
    }

    private function dhikr(int $collectionId, int $id): object
    {
        $dhikr = DB::table('app_dhikrs')->where('collection_id', $collectionId)->find($id);
        abort_unless($dhikr, 404);
        return $dhikr;
    }
}
