<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HomeAdvertisement;
use App\Models\Setting;
use App\Models\Post;
use App\Models\SubCategory;
use App\Models\Video;
use App\Models\Category;
use App\Models\Language;
use App\Helper\Helpers;

class HomeController extends Controller
{
    public function index()
    {
        Helpers::read_json();

        if(!session()->get('session_short_name')) {
            $current_short_name = Language::where('is_default','Yes')->first()->short_name;
        } else {
            $current_short_name = session()->get('session_short_name');
        }

        $current_language_id = Language::where('short_name',$current_short_name)->first()->id;

        $video_data = Video::where('language_id',$current_language_id)->get();
        $home_ad_data = HomeAdvertisement::where('id',1)->first();
        $setting_data = Setting::where('id',1)->first();

        // Only published posts should be fetched for front usage
        $post_data = Post::with('rSubCategory')
            ->where('language_id', $current_language_id)
            ->where('status', 'published')
            ->orderBy('id','desc')
            ->get();

        // Subcategories used on homepage: rPost relationship should already filter to published
        $sub_category_data = SubCategory::with('rPost')
            ->orderBy('sub_category_order','asc')
            ->where('show_on_home','Show')
            ->where('language_id', $current_language_id)
            ->get();

        $category_data = Category::orderBy('category_order','asc')
            ->where('language_id', $current_language_id)
            ->get();

        return view('front.home', compact('home_ad_data', 'setting_data', 'post_data', 'sub_category_data','video_data','category_data'));
    }

    // Search method — matches your existing form (name="text_item")
    public function search(Request $request)
    {
        // ensure translations/constants are loaded (so SEARCH_RESULT, etc exist in blade)
        Helpers::read_json();

        // form input name in your home blade is "text_item" — capture that first
        $query = $request->input('text_item') ?? $request->input('query') ?? '';

        if(!session()->get('session_short_name')) {
            $current_short_name = Language::where('is_default','Yes')->first()->short_name;
        } else {
            $current_short_name = session()->get('session_short_name');
        }
        $current_language_id = Language::where('short_name',$current_short_name)->first()->id;

        // Search only published posts in current language
        $post_data = Post::with('rSubCategory')
            ->where('language_id', $current_language_id)
            ->where('status', 'published')
            ->where(function ($q) use ($query) {
                $q->where('post_title', 'LIKE', "%{$query}%")
                  ->orWhere('post_detail', 'LIKE', "%{$query}%");
            })
            ->orderBy('id','desc')
            ->paginate(10);

        return view('front.search_result', compact('post_data','query'));
    }
}
