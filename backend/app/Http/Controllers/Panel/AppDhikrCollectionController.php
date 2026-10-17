<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Support\AppDhikrCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Admin panel «اذکار برنامه»: collections of adhkar shown in the app. A collection either adds to a
 * built-in app collection (e.g. اذکار صبحگاه) or is a new collection on «اذکار و ادعیه».
 */
class AppDhikrCollectionController extends Controller
{
    public function index()
    {
        $items = DB::table('app_dhikr_collections as c')
            ->select('c.*')
            ->selectSub(fn ($q) => $q->from('app_dhikrs as d')->selectRaw('count(*)')->whereColumn('d.collection_id', 'c.id'), 'dhikrs_count')
            ->orderBy('c.sort_order')->orderBy('c.id')
            ->get();
        return view('panel.app-adhkar.index', compact('items'));
    }

    public function create()
    {
        return view('panel.app-adhkar.form', ['collection' => null]);
    }

    public function store(Request $request)
    {
        $next = (int) DB::table('app_dhikr_collections')->max('sort_order') + 10;
        $id = DB::table('app_dhikr_collections')->insertGetId($this->validated($request) + [
            'sort_order' => $next, 'created_at' => now(), 'updated_at' => now(),
        ]);
        return redirect()->route('panel.app-dhikrs.index', $id)->with('status', 'مجموعه ساخته شد؛ حالا ذکرهایش را اضافه کنید.');
    }

    public function edit(int $id)
    {
        return view('panel.app-adhkar.form', ['collection' => $this->collection($id)]);
    }

    public function update(Request $request, int $id)
    {
        $existing = $this->collection($id);
        DB::table('app_dhikr_collections')->where('id', $id)->update($this->validated($request, $existing) + ['updated_at' => now()]);
        return redirect()->route('panel.app-adhkar.index')->with('status', 'مجموعه به‌روزرسانی شد.');
    }

    public function toggle(int $id)
    {
        $collection = $this->collection($id);
        DB::table('app_dhikr_collections')->where('id', $id)->update(['published' => !$collection->published, 'updated_at' => now()]);
        return back()->with('status', $collection->published ? 'مجموعه از برنامه پنهان شد.' : 'مجموعه در برنامه نمایش داده می‌شود.');
    }

    public function move(int $id, string $direction)
    {
        Reorder::move('app_dhikr_collections', $id, $direction);
        DB::table('app_dhikr_collections')->where('id', $id)->update(['updated_at' => now()]);
        return back();
    }

    public function destroy(int $id)
    {
        $this->collection($id);
        DB::table('app_dhikr_collections')->where('id', $id)->delete(); // adhkar are removed by the cascade
        return back()->with('status', 'مجموعه و ذکرهایش حذف شدند.');
    }

    private function validated(Request $request, ?object $existing = null): array
    {
        $isNew = $request->input('kind') !== 'builtin';
        $data = $request->validate([
            'kind' => 'required|in:builtin,new',
            'builtin_key' => ['nullable', 'required_if:kind,builtin', Rule::in(array_keys(AppDhikrCatalog::BUILT_IN))],
            'title' => 'nullable|required_if:kind,new|string|max:160',
            'description' => 'nullable|string|max:300',
            'icon' => ['required', Rule::in(array_keys(AppDhikrCatalog::ICONS))],
        ], [
            'builtin_key.required_if' => 'مجموعه‌ی برنامه را انتخاب کنید.',
            'title.required_if' => 'عنوان مجموعه‌ی جدید را بنویسید.',
        ], [
            'builtin_key' => 'مجموعه‌ی برنامه', 'title' => 'عنوان', 'description' => 'توضیح کوتاه', 'icon' => 'نماد',
        ]);

        return [
            'builtin_key' => $isNew ? null : $data['builtin_key'],
            'title' => $isNew ? $data['title'] : null,
            'description' => $isNew ? ($data['description'] ?? null) : null,
            'icon' => $data['icon'],
            'published' => $request->boolean('published'),
        ];
    }

    private function collection(int $id): object
    {
        $collection = DB::table('app_dhikr_collections')->find($id);
        abort_unless($collection, 404);
        return $collection;
    }
}
