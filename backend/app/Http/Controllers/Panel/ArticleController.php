<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Admin panel: articles shown in the app's «مقالات» (the `posts` table). Published articles are served
 * to the app by GET /api/app-articles. Content is plain text; blank lines separate paragraphs.
 */
class ArticleController extends Controller
{
    public function index()
    {
        $items = DB::table('posts')->orderByRaw("status = 'published' desc")->orderByRaw('published_at > ? desc', [now()])->orderByDesc('published_at')->orderByDesc('id')->paginate(30);
        return view('panel.articles.index', compact('items'));
    }

    public function create(Request $request)
    {
        $source = $request->filled('translation_of') ? $this->article((int) $request->input('translation_of')) : null;
        $group = $source?->translation_group ?: (string) Str::uuid();
        $translations = DB::table('posts')->where('translation_group', $group)->get(['id', 'locale', 'title']);
        return view('panel.articles.form', ['article' => null, 'locale' => $source ? 'ar' : 'fa', 'group' => $group, 'source' => $source, 'translations' => $translations]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, null);
        DB::table('posts')->insert($data + [
            'slug' => $this->uniqueSlug($request->input('slug')),
            'user_id' => Auth::guard('admin')->id(),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        return redirect()->route('panel.articles.index')->with('status', 'مقاله ذخیره شد.');
    }

    public function edit(int $id)
    {
        $article = $this->article($id);
        $group = $article->translation_group ?: (string) Str::uuid();
        $translations = DB::table('posts')->where('translation_group', $article->translation_group)->get(['id', 'locale', 'title']);
        return view('panel.articles.form', ['article' => $article, 'locale' => $article->locale ?: 'fa', 'group' => $group, 'source' => null, 'translations' => $translations]);
    }

    public function update(Request $request, int $id)
    {
        $article = $this->article($id);
        DB::table('posts')->where('id', $id)->update($this->validated($request, $article) + ['updated_at' => now()]);
        return redirect()->route('panel.articles.index')->with('status', 'مقاله به‌روزرسانی شد.');
    }

    public function toggle(int $id)
    {
        $article = $this->article($id);
        $publish = $article->status !== 'published';
        DB::table('posts')->where('id', $id)->update([
            'status' => $publish ? 'published' : 'draft',
            'published_at' => $publish ? ($article->published_at ?? now()) : $article->published_at,
            'updated_at' => now(),
        ]);
        return back()->with('status', $publish ? 'مقاله منتشر شد.' : 'مقاله به پیش‌نویس برگشت.');
    }

    public function destroy(int $id)
    {
        $this->article($id);
        DB::table('posts')->where('id', $id)->delete();
        return back()->with('status', 'مقاله حذف شد.');
    }

    private function validated(Request $request, ?object $existing): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:200',
            'locale' => 'nullable|in:fa,ar,ckb',
            'translation_group' => 'nullable|uuid',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string|max:200000',
            'publish_at' => 'nullable|date_format:Y-m-d\TH:i',
        ], [], ['title' => 'عنوان', 'excerpt' => 'خلاصه', 'content' => 'متن مقاله', 'publish_at' => 'زمان انتشار']);

        $locale = $data['locale'] ?? $existing?->locale ?? 'fa';
        $group = $data['translation_group'] ?? $existing?->translation_group ?? (string) Str::uuid();
        $duplicate = DB::table('posts')->where('translation_group', $group)->where('locale', $locale)
            ->when($existing, fn ($query) => $query->where('id', '!=', $existing->id))->exists();
        if ($duplicate) {
            throw \Illuminate\Validation\ValidationException::withMessages(['locale' => 'برای این مقاله، نسخه‌ای با این زبان وجود دارد. همان نسخه را ویرایش کنید.']);
        }

        $publish = $request->boolean('published');
        // The form uses Tehran time; a future date schedules the article (the app API hides it until then).
        $publishAt = isset($data['publish_at'])
            ? Carbon::createFromFormat('Y-m-d\TH:i', $data['publish_at'], 'Asia/Tehran')->utc()
            : null;
        return [
            'title' => trim($data['title']),
            'locale' => $locale,
            'translation_group' => $group,
            'excerpt' => isset($data['excerpt']) ? trim($data['excerpt']) : null,
            'content' => trim($data['content']),
            'status' => $publish ? 'published' : 'draft',
            // Keep the first publication date when an article is edited or re-published.
            'published_at' => $publishAt ?? ($publish ? ($existing?->published_at ?? now()) : $existing?->published_at),
        ];
    }

    /** Latin slug (the app's article id); Persian titles get a short random one. */
    private function uniqueSlug(?string $wanted): string
    {
        $base = Str::slug((string) $wanted) ?: 'article-' . Str::lower(Str::random(6));
        $slug = $base;
        for ($i = 2; DB::table('posts')->where('slug', $slug)->exists(); $i++) {
            $slug = "{$base}-{$i}";
        }
        return $slug;
    }

    private function article(int $id): object
    {
        $article = DB::table('posts')->find($id);
        abort_unless($article, 404);
        return $article;
    }
}
