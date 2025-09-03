<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Tag;
use App\Models\Photo;
use Auth;
use DB;

class AuthorPostController extends Controller
{
    public function show()
    {
        $posts = Post::with('rSubCategory.rCategory','rLanguage')
            ->where('author_id', Auth::guard('author')->user()->id)
            ->orderBy('id','desc')
            ->get();
        return view('author.post_show', compact('posts'));
    }

    public function create()
    {
        $sub_categories = SubCategory::with('rCategory')->get();
        // fetch photos so authors can select from gallery
        $photos = Photo::orderBy('id','desc')->get();
        return view('author.post_create', compact('sub_categories','photos'));
    }

    public function store(Request $request)
    {
        // Validate basic fields; photo can be uploaded OR selected from gallery
        $validator = \Validator::make($request->all(), [
            'post_title'   => 'required',
            'post_detail'  => 'required',
            'post_photo'   => 'nullable|image|mimes:jpg,jpeg,png,gif',
            'photo_id'     => 'nullable|exists:photos,id'
        ]);

        // Require at least one of upload OR gallery selection
        if (!$request->hasFile('post_photo') && !$request->filled('photo_id')) {
            return back()->withErrors(['post_photo' => 'Please upload a photo or select one from the gallery.'])->withInput();
        }

        $q = DB::select("SHOW TABLE STATUS LIKE 'posts'");
        $ai_id = $q[0]->Auto_increment;

        // Handle upload if present
        $final_name = null;
        if ($request->hasFile('post_photo')) {
            $now = time();
            $ext = $request->file('post_photo')->extension();
            $final_name = 'post_photo_'.$now.'.'.$ext;
            $request->file('post_photo')->move(public_path('uploads/'), $final_name);
        } elseif ($request->filled('photo_id')) {
            // Use selected photo from gallery
            $photo = Photo::find($request->photo_id);
            if ($photo) {
                $final_name = $photo->photo;
            }
        }

        $post = new Post();
        $post->sub_category_id = $request->sub_category_id;
        $post->post_title = $request->post_title;
        $post->post_detail = $request->post_detail;
        $post->post_photo = $final_name;
        $post->visitors = 1;
        $post->author_id = Auth::guard('author')->user()->id;
        $post->admin_id = 0;
        $post->is_share = $request->is_share ?? 0;
        $post->is_comment = $request->is_comment ?? 0;
        $post->language_id = $request->language_id ?? 1; // default language id (adjust if needed)
        $post->status = 'pending'; // AUTHOR posts default to pending
        $post->save();

        // Tags: use the actual post id to avoid mismatch
        if($request->filled('tags')) {
            $tags_array_new = array_unique(array_map('trim', explode(',', $request->tags)));
            foreach($tags_array_new as $tag_name){
                if ($tag_name === '') continue;
                $tag = new Tag();
                $tag->post_id = $post->id;
                $tag->tag_name = $tag_name;
                $tag->save();
            }
        }

        // Mail sending disabled (you asked to deactivate)
        return redirect()->route('author_post_show')->with('success', 'Post submitted successfully and is pending admin approval.');
    }

    public function edit($id)
    {
        $test = Post::where('id', $id)
            ->where('author_id', Auth::guard('author')->user()->id)
            ->count();
        if(!$test) {
            return redirect()->route('author_home');
        }

        $sub_categories = SubCategory::with('rCategory')->get();
        $existing_tags = Tag::where('post_id', $id)->get();
        $post_single = Post::where('id', $id)->first();
        // supply photos for gallery selection on edit screen
        $photos = Photo::orderBy('id','desc')->get();

        return view('author.post_edit', compact('post_single','sub_categories','existing_tags','photos'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'post_title' => 'required',
            'post_detail' => 'required'
        ]);

        $post = Post::where('id', $id)->first();

        // If a new file is uploaded, validate and replace
        if($request->hasFile('post_photo')) {
            $request->validate([
                'post_photo' => 'image|mimes:jpg,jpeg,png,gif'
            ]);
            if($post->post_photo && file_exists(public_path('uploads/'.$post->post_photo))){
                @unlink(public_path('uploads/'.$post->post_photo));
            }

            $now = time();
            $ext = $request->file('post_photo')->extension();
            $final_name = 'post_photo_'.$now.'.'.$ext;
            $request->file('post_photo')->move(public_path('uploads/'), $final_name);

            $post->post_photo = $final_name;
        } elseif ($request->filled('photo_id')) {
            // If an existing gallery photo was selected, use that filename
            $photo = Photo::find($request->photo_id);
            if ($photo) {
                // no file deletion because we're switching to gallery image (just overwrite field)
                $post->post_photo = $photo->photo;
            }
        }
        // Otherwise keep existing $post->post_photo

        $post->sub_category_id = $request->sub_category_id;
        $post->post_title = $request->post_title;
        $post->post_detail = $request->post_detail;
        $post->is_share = $request->is_share ?? 0;
        $post->is_comment = $request->is_comment ?? 0;
        $post->language_id = $request->language_id ?? 1; // default English
        $post->status = 'pending'; // keep pending until admin approves after edit
        $post->save();

        if($request->filled('tags')) {
            $tags_array = array_map('trim', explode(',', $request->tags));
            foreach($tags_array as $tag_name){
                if ($tag_name === '') continue;
                $total = Tag::where('post_id', $id)->where('tag_name', $tag_name)->count();
                if(!$total) {
                    $tag = new Tag();
                    $tag->post_id = $id;
                    $tag->tag_name = $tag_name;
                    $tag->save();
                }
            }
        }

        return redirect()->route('author_post_show')->with('success', 'Post updated successfully and is pending admin approval.');
    }

    public function delete_tag($id, $post_id)
    {
        $tag = Tag::where('id', $id)->first();
        if($tag) $tag->delete();
        // keep your existing route naming
        return redirect()->route('author.post_edit', $post_id)->with('success', 'Tag deleted successfully.');
    }

    public function delete($id)
    {
        $test = Post::where('id', $id)
            ->where('author_id', Auth::guard('author')->user()->id)
            ->count();
        if(!$test) {
            return redirect()->route('author_home');
        }

        $post = Post::where('id', $id)->first();
        if(file_exists(public_path('uploads/'.$post->post_photo))){
            @unlink(public_path('uploads/'.$post->post_photo));
        }
        $post->delete();

        Tag::where('post_id', $id)->delete();

        return redirect()->route('author_post_show')->with('success', 'Post deleted successfully.');
    }
}
