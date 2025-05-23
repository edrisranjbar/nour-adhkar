<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Post::query()
            ->with(['user:id,name,avatar', 'categories'])
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());

        // Filter by category
        if ($request->has('category')) {
            $query->whereHas('categories', function($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Search by title or content
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $posts = $query->orderBy('published_at', 'desc')
            ->paginate(10);

        return response()->json([
            'data' => $posts->items(),
            'success' => true,
            'meta' => [
                'current_page' => $posts->currentPage(),
                'total' => $posts->total(),
                'per_page' => $posts->perPage(),
                'last_page' => $posts->lastPage()
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255|unique:posts',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'image' => 'nullable|string',
            'status' => 'required|in:draft,published',
            'published_at' => 'nullable|date',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();
        
        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
            // Check if slug already exists
            $count = Post::where('slug', 'LIKE', $data['slug'] . '%')->count();
            if ($count > 0) {
                $data['slug'] = $data['slug'] . '-' . ($count + 1);
            }
        }
        
        // Set published_at if status is published and not provided
        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        // Set user_id
        $data['user_id'] = Auth::id();
        
        // Extract category IDs
        $categoryIds = $data['category_ids'] ?? [];
        unset($data['category_ids']);
        
        $post = Post::create($data);
        
        // Attach categories
        if (!empty($categoryIds)) {
            $post->categories()->attach($categoryIds);
        }

        return response()->json([
            'success' => true,
            'message' => 'مقاله با موفقیت ایجاد شد',
            'post' => $post->load(['categories', 'user:id,name,avatar'])
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($slug)
    {
        $post = Post::where('slug', $slug)
            ->where('status', 'published')
            ->with(['user', 'categories'])
            ->firstOrFail();

        // Increment views
        $post->incrementViews();

        return response()->json($post);
    }

    public function getViews($id)
    {
        $post = Post::findOrFail($id);
        return response()->json(['views' => $post->views]);
    }

    public function getTotalViews()
    {
        $totalViews = Post::sum('views');
        return response()->json(['total_views' => $totalViews]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('posts')->ignore($post->id),
            ],
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'image' => 'nullable|string',
            'status' => 'required|in:draft,published',
            'published_at' => 'nullable|date',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        // Update slug if title changed
        if ($post->isDirty('title') && empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
            $count = Post::where('slug', 'LIKE', $data['slug'] . '%')
                ->where('id', '!=', $post->id)
                ->count();
            if ($count > 0) {
                $data['slug'] = $data['slug'] . '-' . ($count + 1);
            }
        }

        // Update published_at if status changed to published
        if ($post->isDirty('status') && $data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        // Extract category IDs
        $categoryIds = $data['category_ids'] ?? null;
        unset($data['category_ids']);

        $post->update($data);

        // Sync categories if provided
        if ($categoryIds !== null) {
            $post->categories()->sync($categoryIds);
        }

        return response()->json([
            'success' => true,
            'message' => 'مقاله با موفقیت بروزرسانی شد',
            'post' => $post->load(['categories', 'user:id,name,avatar'])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        $post->delete();

        return response()->json([
            'success' => true,
            'message' => 'مقاله با موفقیت حذف شد'
        ]);
    }

    /**
     * Upload featured image for a post
     */
    public function uploadFeaturedImage(Request $request, Post $post)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {
            if ($post->featured_image) {
                Storage::disk('public')->delete($post->featured_image);
            }

            $path = $request->file('image')->store('posts', 'public');
            $post->featured_image = $path;
            $post->save();

            return response()->json([
                'success' => true,
                'url' => Storage::url($path)
            ]);
        } catch (\Exception $e) {
            Log::error('Featured image upload failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Featured image upload failed'
            ], 500);
        }
    }

    /**
     * Get admin list of posts (including drafts)
     */
    public function adminIndex()
    {
        $posts = Post::orderBy('created_at', 'desc')
            ->with(['user:id,name,avatar', 'categories'])
            ->paginate(10);

        return response()->json([
            'success' => true,
            'posts' => $posts
        ]);
    }

    /**
     * Get related posts
     */
    public function related(Post $post)
    {
        $relatedPosts = Post::where('id', '!=', $post->id)
            ->where(function($query) use ($post) {
                $query->where('category_id', $post->category_id)
                    ->orWhereHas('categories', function($q) use ($post) {
                        $q->whereIn('categories.id', $post->categories->pluck('id'));
                    });
            })
            ->published()
            ->orderBy('published_at', 'desc')
            ->limit(3)
            ->get();

        return response()->json([
            'success' => true,
            'related_posts' => $relatedPosts
        ]);
    }

    /**
     * Upload file for post
     */
    public function uploadFile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $file = $request->file('file');
            $path = $file->store('post-images', 'public');
            
            return response()->json([
                'success' => true,
                'url' => Storage::url($path)
            ]);
        } catch (\Exception $e) {
            Log::error('File upload failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'File upload failed'
            ], 500);
        }
    }
} 