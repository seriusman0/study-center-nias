<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentWebController extends Controller
{
    public function store(Request $request, Blog $blog)
    {
        $request->validate(['content' => 'required|string|max:1000']);

        $blog->comments()->create([
            'user_id' => auth()->id(),
            'content' => $request->content,
            'parent_id' => $request->parent_id,
        ]);

        return back()->with('success', 'Komentar terkirim!');
    }

    public function destroy(Comment $comment)
    {
        $user = auth()->user();
        if ($user->id !== $comment->user_id && ! $user->isAdmin()) {
            abort(403);
        }

        $comment->delete();
        return back()->with('success', 'Komentar dihapus.');
    }
}
