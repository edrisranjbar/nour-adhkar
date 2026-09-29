<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/** Admin panel «علما و سخنرانی‌ها»: the scholars shown in the app. */
class ScholarController extends Controller
{
    public function index()
    {
        $items = DB::table('scholars as s')
            ->select('s.*')
            ->selectSub(fn ($q) => $q->from('lectures as l')->selectRaw('count(*)')->whereColumn('l.scholar_id', 's.id'), 'lectures_count')
            ->orderBy('s.sort_order')->orderBy('s.id')
            ->get();
        return view('panel.scholars.index', compact('items'));
    }

    public function create()
    {
        return view('panel.scholars.form', ['scholar' => null]);
    }

    public function store(Request $request)
    {
        $next = (int) DB::table('scholars')->max('sort_order') + 10;
        DB::table('scholars')->insert($this->validated($request) + ['sort_order' => $next, 'created_at' => now(), 'updated_at' => now()]);
        return redirect()->route('panel.scholars.index')->with('status', 'استاد اضافه شد.');
    }

    public function edit(int $id)
    {
        $scholar = DB::table('scholars')->find($id);
        abort_unless($scholar, 404);
        return view('panel.scholars.form', compact('scholar'));
    }

    public function update(Request $request, int $id)
    {
        $existing = DB::table('scholars')->find($id);
        abort_unless($existing, 404);
        DB::table('scholars')->where('id', $id)->update($this->validated($request, $existing) + ['updated_at' => now()]);
        return redirect()->route('panel.scholars.index')->with('status', 'اطلاعات استاد به‌روزرسانی شد.');
    }

    public function toggle(int $id)
    {
        $scholar = DB::table('scholars')->find($id);
        abort_unless($scholar, 404);
        DB::table('scholars')->where('id', $id)->update(['published' => !$scholar->published, 'updated_at' => now()]);
        return back()->with('status', $scholar->published ? 'استاد از برنامه پنهان شد.' : 'استاد در برنامه نمایش داده می‌شود.');
    }

    public function move(int $id, string $direction)
    {
        Reorder::move('scholars', $id, $direction);
        return back();
    }

    public function destroy(int $id)
    {
        // Lectures are removed by the foreign key cascade; delete their uploaded files first.
        foreach (DB::table('lectures')->where('scholar_id', $id)->whereNotNull('audio_path')->pluck('audio_path') as $path) {
            Storage::disk('public')->delete($path);
        }
        if ($photo = DB::table('scholars')->where('id', $id)->value('photo_path')) {
            Storage::disk('public')->delete($photo);
        }
        DB::table('scholars')->where('id', $id)->delete();
        return back()->with('status', 'استاد و سخنرانی‌هایش حذف شدند.');
    }

    private function validated(Request $request, ?object $existing = null): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'slug' => ['required', 'string', 'max:40', 'regex:/^[a-z0-9_-]+$/', Rule::unique('scholars', 'slug')->ignore($existing?->id)],
            'tagline' => 'nullable|string|max:160',
            'bio' => 'nullable|string|max:5000',
            'hue' => 'required|integer|min:0|max:360',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
        ], [
            'slug.regex' => 'شناسه فقط می‌تواند حروف کوچک انگلیسی، عدد، خط تیره و زیرخط داشته باشد.',
            'slug.unique' => 'این شناسه قبلاً استفاده شده است.',
            'photo.image' => 'عکس باید تصویر باشد.',
            'photo.mimes' => 'عکس باید jpg، png یا webp باشد.',
            'photo.max' => 'حجم عکس حداکثر ۳ مگابایت است.',
        ], [
            'name' => 'نام', 'slug' => 'شناسه', 'tagline' => 'زیرعنوان', 'bio' => 'معرفی', 'hue' => 'رنگ جلد', 'photo' => 'عکس',
        ]);
        unset($data['photo']);

        if ($request->hasFile('photo')) {
            // A new photo replaces the previous file.
            if ($existing?->photo_path) {
                Storage::disk('public')->delete($existing->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('scholars', 'public');
        } elseif ($request->boolean('remove_photo') && $existing?->photo_path) {
            Storage::disk('public')->delete($existing->photo_path);
            $data['photo_path'] = null;
        }

        return $data + ['published' => $request->boolean('published')];
    }
}
