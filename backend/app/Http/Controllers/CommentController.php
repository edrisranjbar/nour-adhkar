<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class CommentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Comment::with(['user', 'post'])
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->search, function ($query, $search) {
                return $query->where('content', 'like', "%{$search}%");
            })
            ->orderBy($request->sort_by ?? 'created_at', $request->sort_order ?? 'desc');

        $comments = $query->paginate($request->per_page ?? 10);

        return response()->json([
            'success' => true,
            'comments' => $comments
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'required|exists:posts,id',
            'content' => 'required|string|min:3',
            'author_name' => 'required_if:user_id,null|string|max:255',
            'author_email' => 'required_if:user_id,null|email|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();
        
        if (Auth::check()) {
            $data['user_id'] = Auth::id();
        }

        $comment = Comment::create($data);

        return response()->json([
            'success' => true,
            'message' => 'نظر شما با موفقیت ثبت شد و پس از تایید نمایش داده خواهد شد.',
            'comment' => $comment
        ], 201);
    }

    public function update(Request $request, Comment $comment)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,approved,rejected'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $comment->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'وضعیت نظر با موفقیت بروزرسانی شد',
            'comment' => $comment
        ]);
    }

    public function destroy(Comment $comment): JsonResponse
    {
        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'نظر با موفقیت حذف شد'
        ]);
    }

    public function getPostComments($postId)
    {
        $comments = Comment::with(['user:id,name,avatar'])
            ->where('post_id', $postId)
            ->where('status', 'approved')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'comments' => $comments
        ]);
    }

    public function getPendingCount(): JsonResponse
    {
        $count = Comment::where('status', 'pending')->count();
        
        return response()->json([
            'success' => true,
            'count' => $count
        ]);
    }

    public function approve(Comment $comment): JsonResponse
    {
        $comment->update(['status' => 'approved']);

        return response()->json([
            'success' => true,
            'message' => 'Comment approved successfully'
        ]);
    }

    public function reject(Comment $comment): JsonResponse
    {
        $comment->update(['status' => 'rejected']);

        return response()->json([
            'success' => true,
            'message' => 'Comment rejected successfully'
        ]);
    }
}
