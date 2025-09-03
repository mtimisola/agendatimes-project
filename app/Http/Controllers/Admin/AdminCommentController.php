<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Comment;

class AdminCommentController extends Controller
{
    public function index()
    {
        $comments = Comment::with('post')->orderBy('id', 'desc')->paginate(20);
        return view('admin.comments_index', compact('comments'));
    }

    public function delete($id)
    {
        Comment::where('id', $id)->delete();
        return redirect()->route('admin_comments_index')->with('success', 'Comment deleted successfully.');
    }
}
