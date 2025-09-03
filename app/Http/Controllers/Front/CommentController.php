<?php
namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Post;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'post_id' => 'required|integer|exists:posts,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'comment' => 'required|string|max:1000',
            'parent_id' => 'nullable|integer|exists:comments,id',
        ]);

        Comment::create($request->only(['post_id', 'name', 'email', 'comment', 'parent_id']));

        return redirect()->back()->with('success', 'Comment submitted successfully!');
    }

    public function like($id)
    {
        $comment = Comment::findOrFail($id);
        $comment->increment('likes');
        return redirect()->back();
    }

    public function dislike($id)
    {
        $comment = Comment::findOrFail($id);
        $comment->increment('dislikes');
        return redirect()->back();
    }
}
