<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Comment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Blog $blog): JsonResponse
    {
        $comments = $blog->rootComments()
            ->with(['user:id,name,username,avatar', 'replies.user:id,name,username,avatar'])
            ->latest()
            ->get();

        return response()->json($comments);
    }

    public function store(Request $request, Blog $blog): JsonResponse
    {
        $validated = $request->validate([
            'content' => ['required', 'string', 'max:2000'],
            'parent_id' => ['nullable', 'exists:comments,id'],
        ]);

        $comment = Comment::create([
            'blog_id' => $blog->id,
            'user_id' => $request->user()->id,
            'parent_id' => $validated['parent_id'] ?? null,
            'content' => $validated['content'],
        ]);

        return response()->json($comment->load('user:id,name,username,avatar'), 201);
    }

    public function destroy(Request $request, Comment $comment): JsonResponse
    {
        $user = $request->user();
        if ($comment->user_id !== $user->id && !$user->isAdmin()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $comment->delete();
        return response()->json(['message' => 'Komentar dihapus.']);
    }
}
