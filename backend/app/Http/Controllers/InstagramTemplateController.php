<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InstagramTemplateController extends Controller
{
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:80',
            'is_default' => 'sometimes|boolean',
            'design' => 'required|array:background,text,muted,font,font_size,line_height,margin,logo_size,layout',
            'design.background' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'design.text' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'design.muted' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'design.font' => 'required|in:vazirmatn,vazirmatn-bold,noto-naskh',
            'design.font_size' => 'required|integer|between:36,84',
            'design.line_height' => 'required|numeric|between:1.4,2.2',
            'design.margin' => 'required|integer|between:80,180',
            'design.logo_size' => 'required|integer|between:48,120',
            'design.layout' => 'required|in:centered,upper,framed',
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        return DB::transaction(function () use ($data) {
            DB::table('instagram_templates')->orderBy('id')->lockForUpdate()->get();
            if ($data['is_default'] ?? false) DB::table('instagram_templates')->update(['is_default' => false]);
            $id = DB::table('instagram_templates')->insertGetId([
                'name' => $data['name'], 'design' => json_encode($data['design']), 'is_default' => $data['is_default'] ?? false,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            return response()->json(['data' => $this->template($id)], 201);
        });
    }

    public function update(Request $request, int $id)
    {
        $data = $this->validated($request);
        return DB::transaction(function () use ($data, $id) {
            $templates = DB::table('instagram_templates')->orderBy('id')->lockForUpdate()->get();
            $template = $templates->firstWhere('id', $id);
            abort_unless($template, 404);
            abort_if($template->is_default && array_key_exists('is_default', $data) && !$data['is_default'], 422, 'Choose another default template first.');
            if ($data['is_default'] ?? false) DB::table('instagram_templates')->update(['is_default' => false]);
            DB::table('instagram_templates')->where('id', $id)->update([
                'name' => $data['name'], 'design' => json_encode($data['design']),
                'is_default' => $data['is_default'] ?? $template->is_default, 'updated_at' => now(),
            ]);
            return response()->json(['data' => $this->template($id)]);
        });
    }

    public function destroy(int $id)
    {
        return DB::transaction(function () use ($id) {
            $templates = DB::table('instagram_templates')->orderBy('id')->lockForUpdate()->get();
            $template = $templates->firstWhere('id', $id);
            abort_unless($template, 404);
            abort_if($template->is_default, 422, 'Choose another default template before deleting this one.');
            // Posts keep their design snapshot even if their originating template is deleted.
            DB::table('instagram_templates')->where('id', $id)->delete();
            return response()->noContent();
        });
    }

    private function template(int $id): object
    {
        $template = DB::table('instagram_templates')->where('id', $id)->first();
        $template->design = json_decode($template->design, true);
        $template->is_default = (bool) $template->is_default;
        return $template;
    }
}
