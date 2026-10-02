<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AppVersionController extends Controller
{
    public function index()
    {
        $items = DB::table('app_versions')->orderByDesc('version_code')->paginate(30);
        $latest = DB::table('app_versions')->where('published', true)->max('version_code');
        $minRequired = DB::table('app_versions')->where('published', true)->where('is_required', true)->max('version_code');

        return view('panel.versions.index', compact('items', 'latest', 'minRequired'));
    }

    public function create()
    {
        return view('panel.versions.form', ['version' => null]);
    }

    public function store(Request $request)
    {
        DB::table('app_versions')->insert($this->validated($request) + ['created_at' => now(), 'updated_at' => now()]);
        return redirect()->route('panel.versions.index')->with('status', 'نسخه ذخیره شد.');
    }

    public function edit(int $id)
    {
        $version = DB::table('app_versions')->find($id);
        abort_unless($version, 404);
        return view('panel.versions.form', compact('version'));
    }

    public function update(Request $request, int $id)
    {
        abort_unless(DB::table('app_versions')->where('id', $id)->exists(), 404);
        DB::table('app_versions')->where('id', $id)->update($this->validated($request, $id) + ['updated_at' => now()]);
        return redirect()->route('panel.versions.index')->with('status', 'نسخه به‌روزرسانی شد.');
    }

    public function toggle(int $id)
    {
        $version = DB::table('app_versions')->find($id);
        abort_unless($version, 404);
        DB::table('app_versions')->where('id', $id)->update(['published' => !$version->published, 'updated_at' => now()]);
        return back()->with('status', $version->published ? 'نسخه به پیش‌نویس برگشت.' : 'نسخه منتشر شد.');
    }

    public function destroy(int $id)
    {
        DB::table('app_versions')->where('id', $id)->delete();
        return back()->with('status', 'نسخه حذف شد.');
    }

    private function validated(Request $request, ?int $id = null): array
    {
        $data = $request->validate([
            'version_code' => ['required', 'integer', 'min:1', 'max:2100000000', Rule::unique('app_versions', 'version_code')->ignore($id)],
            'version_name' => 'required|string|max:40',
            'changelog' => 'required|string|max:5000',
            'release_date' => 'required|date',
        ], [
            'version_code.unique' => 'این کد نسخه قبلاً ثبت شده است.',
        ], [
            'version_code' => 'کد نسخه',
            'version_name' => 'نام نسخه',
            'changelog' => 'تغییرات',
            'release_date' => 'تاریخ انتشار',
        ]);
        return $data + [
            'is_required' => $request->boolean('is_required'),
            'published' => $request->boolean('published'),
        ];
    }
}
