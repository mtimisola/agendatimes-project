<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\SubCategory;
use App\Models\Tag;
use App\Models\Author;
use App\Models\Admin as AdminModel;
use App\Models\Subscriber;
use App\Models\Photo;
use App\Mail\Websitemail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;

class AdminPostController extends Controller
{
    public function show()
    {
        $posts = Post::with('rSubCategory.rCategory','rLanguage')->orderBy('id','desc')->get();

        $authorIds = $posts->pluck('author_id')->filter()->unique()->values()->all();
        $adminIds  = $posts->pluck('admin_id')->filter()->unique()->values()->all();

        $authors = collect([]);
        $admins  = collect([]);

        if (!empty($authorIds)) {
            $authors = Author::whereIn('id', $authorIds)->get()->keyBy('id');
        }
        if (!empty($adminIds)) {
            $admins = AdminModel::whereIn('id', $adminIds)->get()->keyBy('id');
        }

        return view('admin.post_show', compact('posts','authors','admins'));
    }

    public function create()
    {
        $sub_categories = SubCategory::with('rCategory')->get();
        $photos = Photo::orderBy('id','desc')->get(); // pass photos for gallery selection
        return view('admin.post_create', compact('sub_categories','photos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'post_title'      => 'required',
            'post_detail'     => 'required',
            'sub_category_id' => 'required',
            'post_photo'      => 'nullable|image|mimes:jpg,jpeg,png,gif',
            'photo_id'        => 'nullable|integer',
        ]);

        // Determine final image filename:
        $final_name = null;

        // If author selected existing gallery photo
        if ($request->filled('photo_id')) {
            $gallery = Photo::find($request->photo_id);
            if ($gallery) {
                $final_name = $gallery->photo; // filename stored in photos table
            }
        }

        // If an upload was provided, it overrides selection
        if ($request->hasFile('post_photo')) {
            $ext = $request->file('post_photo')->extension();
            $final_name = 'post_' . time() . '.' . $ext;
            $request->file('post_photo')->move(public_path('uploads/'), $final_name);
        }

        if (!$final_name) {
            return back()->withInput()->withErrors(['post_photo' => 'Please upload an image or select one from gallery.']);
        }

        $post = new Post();
        $post->post_title       = $request->post_title;
        $post->post_detail      = $request->post_detail;
        $post->sub_category_id  = $request->sub_category_id;
        $post->post_photo       = $final_name;
        $post->visitors         = $request->visitors ?? 1;
        $post->author_id        = 0; // created by Admin here
        $post->admin_id         = Auth::guard('admin')->user()->id;
        $post->is_share         = $request->is_share ?? 0;
        $post->is_comment       = $request->is_comment ?? 0;
        $post->language_id      = $request->language_id ?? null;

        if (Schema::hasColumn('posts','headline_section')) {
            $post->headline_section = $request->headline_section ?: 0;
        }
        if (Schema::hasColumn('posts','status')) {
            $post->status = $request->status ?? 'published';
        }

        $post->save();

        // Save tags (comma separated string or array)
        if ($request->filled('tags')) {
            $tags_input = $request->tags;
            $tags_array = is_array($tags_input) ? $tags_input : explode(',', $tags_input);
            $tags_clean = [];
            foreach ($tags_array as $t) {
                $t = trim($t);
                if ($t !== '') $tags_clean[] = $t;
            }
            $tags_clean = array_values(array_unique($tags_clean));
            foreach ($tags_clean as $tag_name) {
                $tag = new Tag();
                $tag->post_id = $post->id;
                $tag->tag_name = $tag_name;
                $tag->save();
            }
        }

        // Optionally send to subscribers (keeps prior behaviour)
        if ($request->subscriber_send_option == 1) {
            $subject = 'A new post is published';
            $message = 'Hi, A new post is published into our website. Please go to see that post:<br>';
            $message .= '<a target="_blank" href="'.route('news_detail',$post->id).'">';
            $message .= $request->post_title;
            $message .= '</a>';

            $subscribers = Subscriber::where('status','Active')->get();
            foreach($subscribers as $row) {
                try {
                    Mail::to($row->email)->send(new Websitemail($subject,$message));
                } catch (\Exception $e) {
                    // ignore mail failures
                }
            }
        }

        return redirect()->route('admin_post_show')->with('success', 'Post created successfully.');
    }

    public function edit($id)
    {
        // Allow admin to edit any post
        $post_single = Post::findOrFail($id);
        $sub_categories = SubCategory::with('rCategory')->get();
        $existing_tags = Tag::where('post_id',$id)->get();
        $photos = Photo::orderBy('id','desc')->get(); // pass photos for gallery selection

        return view('admin.post_edit', compact('post_single','sub_categories','existing_tags','photos'));
    }

    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        $request->validate([
            'post_title'      => 'required',
            'post_detail'     => 'required',
            'sub_category_id' => 'required',
            'post_photo'      => 'nullable|image|mimes:jpg,jpeg,png,gif',
            'photo_id'        => 'nullable|integer',
        ]);

        // If a new file was uploaded -> replace old file
        if ($request->hasFile('post_photo')) {
            if ($post->post_photo && file_exists(public_path('uploads/'.$post->post_photo))) {
                @unlink(public_path('uploads/'.$post->post_photo));
            }
            $ext = $request->file('post_photo')->extension();
            $final_name = 'post_' . time() . '.' . $ext;
            $request->file('post_photo')->move(public_path('uploads/'), $final_name);
            $post->post_photo = $final_name;
        } elseif ($request->filled('photo_id')) {
            // If admin selected an existing gallery photo, set the filename (do NOT unlink)
            $gallery = Photo::find($request->photo_id);
            if ($gallery) {
                $post->post_photo = $gallery->photo;
            }
        }
        // else: no change, keep existing post_photo

        $post->post_title       = $request->post_title;
        $post->post_detail      = $request->post_detail;
        $post->sub_category_id  = $request->sub_category_id;
        $post->is_share         = $request->is_share ?? 0;
        $post->is_comment       = $request->is_comment ?? 0;
        $post->language_id      = $request->language_id ?? $post->language_id;

        if (Schema::hasColumn('posts','status') && $request->has('status')) {
            $post->status = in_array($request->status, ['published','pending','draft']) ? $request->status : $post->status;
        }

        if (Schema::hasColumn('posts','headline_section')) {
            $post->headline_section = $request->headline_section ?: 0;
        }

        $post->save();

        // append new tags (don't duplicate)
        if ($request->filled('tags')) {
            $tags_input = $request->tags;
            $tags_array = is_array($tags_input) ? $tags_input : explode(',', $tags_input);
            foreach ($tags_array as $t) {
                $t = trim($t);
                if ($t === '') continue;
                $exists = Tag::where('post_id', $id)->where('tag_name', $t)->count();
                if (!$exists) {
                    $tag = new Tag();
                    $tag->post_id = $id;
                    $tag->tag_name = $t;
                    $tag->save();
                }
            }
        }

        return redirect()->route('admin_post_show')->with('success', 'Post updated successfully.');
    }

    public function delete_tag($id,$id1)
    {
        $tag = Tag::where('id',$id)->first();
        if ($tag) $tag->delete();
        return redirect()->route('admin_post_edit',$id1)->with('success', 'Tag deleted successfully.');
    }

    public function delete($id)
    {
        $post = Post::findOrFail($id);
        if ($post->post_photo && file_exists(public_path('uploads/'.$post->post_photo))) {
            @unlink(public_path('uploads/'.$post->post_photo));
        }
        Tag::where('post_id',$id)->delete();
        $post->delete();
        return redirect()->route('admin_post_show')->with('success', 'Post deleted successfully.');
    }
}
