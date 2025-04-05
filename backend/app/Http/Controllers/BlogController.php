<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BlogController extends Controller
{
    public function index()
    {
        $posts = BlogPost::published()
            ->orderBy('published_at', 'desc')
            ->with('user:id,name,avatar')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'posts' => $posts
        ]);
    }

    public function show($slug)
    {
        $post = BlogPost::where('slug', $slug)
            ->with('user:id,name,avatar')
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'post' => $post
        ]);
    }

    // Admin methods (should be protected by middleware)
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string',
            'image' => 'nullable|string',
            'status' => 'in:draft,published',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $slug = Str::slug($request->title);
        
        // Check if slug already exists
        $count = BlogPost::where('slug', 'LIKE', $slug . '%')->count();
        if ($count > 0) {
            $slug = $slug . '-' . ($count + 1);
        }

        $post = new BlogPost();
        $post->title = $request->title;
        $post->slug = $slug;
        $post->content = $request->content;
        $post->excerpt = $request->excerpt;
        $post->image = $request->image;
        $post->user_id = Auth::id();
        $post->status = $request->status ?? 'draft';
        
        if ($post->status === 'published' && empty($post->published_at)) {
            $post->published_at = now();
        }
        
        $post->save();

        return response()->json([
            'success' => true,
            'post' => $post
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $post = BlogPost::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string',
            'image' => 'nullable|string',
            'status' => 'in:draft,published',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Update the post
        $post->title = $request->title;
        $post->content = $request->content;
        $post->excerpt = $request->excerpt;
        $post->image = $request->image;
        
        // Only update status if it changed
        if ($request->has('status') && $post->status !== $request->status) {
            $post->status = $request->status;
            
            // If publishing for the first time
            if ($post->status === 'published' && empty($post->published_at)) {
                $post->published_at = now();
            }
        }
        
        $post->save();

        return response()->json([
            'success' => true,
            'post' => $post
        ]);
    }

    public function destroy($id)
    {
        $post = BlogPost::findOrFail($id);
        $post->delete();

        return response()->json([
            'success' => true,
            'message' => 'Post deleted successfully'
        ]);
    }

    // Admin endpoints
    public function adminIndex()
    {
        $posts = BlogPost::with('user:id,name,avatar')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'posts' => $posts
        ]);
    }

    public function adminShow($id)
    {
        $post = BlogPost::findOrFail($id);

        return response()->json([
            'success' => true,
            'post' => $post
        ]);
    }

    public function related($id)
    {
        $post = BlogPost::findOrFail($id);

        // Get related posts (simple implementation - you can enhance this)
        $relatedPosts = BlogPost::published()
            ->where('id', '!=', $id)
            ->orderBy('published_at', 'desc')
            ->limit(3)
            ->get();

        return response()->json([
            'success' => true,
            'posts' => $relatedPosts
        ]);
    }
} 