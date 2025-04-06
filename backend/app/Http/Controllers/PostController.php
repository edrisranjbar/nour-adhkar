<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Implement the logic to retrieve a list of posts
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
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
        }
        
        // Set published_at if status is published and not provided
        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }
        
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
            'post' => $post->load('categories')
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        // Implement the logic to retrieve a single post
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
        
        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }
        
        // Set published_at if status is published and not provided
        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }
        
        // Extract category IDs
        $categoryIds = $data['category_ids'] ?? [];
        unset($data['category_ids']);
        
        $post->update($data);
        
        // Sync categories
        $post->categories()->sync($categoryIds);

        return response()->json([
            'success' => true,
            'message' => 'مقاله با موفقیت بروزرسانی شد',
            'post' => $post->load('categories')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        // Implement the logic to delete a post
    }
} 