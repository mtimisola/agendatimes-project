<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Tag;
use App\Models\Admin;
use App\Models\Author;
use App\Helper\Helpers;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function detail($id)
    {
        Helpers::read_json();

        $tag_data = Tag::where('post_id',$id)->get();

        $post_detail = Post::with('rSubCategory')->where('id',$id)->firstOrFail();

        // If post is not published, only admin or the original author may view it
        if($post_detail->status !== 'published') {
            $canView = false;
            // admin
            if(Auth::guard('admin')->check() && Auth::guard('admin')->user()->id == $post_detail->admin_id) {
                $canView = true;
            }
            // author (guest)
            if(Auth::guard('author')->check() && Auth::guard('author')->user()->id == $post_detail->author_id) {
                $canView = true;
            }
            if(!$canView) {
                abort(404); // hide from public
            }
        }

        if($post_detail->author_id == 0)
        {
            $user_data = Admin::where('id',$post_detail->admin_id)->first();
        }
        else
        {
            $user_data = Author::where('id',$post_detail->author_id)->first();
        }

        // Update page view count only for published posts (or if admin/author is viewing)
        if($post_detail->status === 'published') {
            $post_detail->visitors = ($post_detail->visitors ?? 0) + 1;
            $post_detail->save();
        }

        $related_post_array = Post::with('rSubCategory')
            ->where('sub_category_id',$post_detail->sub_category_id)
            ->where('status','published')
            ->orderBy('id','desc')
            ->get();

        return view('front.post_detail', compact('post_detail', 'user_data', 'tag_data','related_post_array'));
    }
}
