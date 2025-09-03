@if(!session()->get('session_short_name'))
    @php
    $current_short_name = $global_short_name;
    @endphp
@else
    @php
    $current_short_name = session()->get('session_short_name');
    @endphp
@endif
@php
    $current_language = \App\Models\Language::where('short_name',$current_short_name)->first();
    $current_language_id = $current_language ? $current_language->id : 1;
@endphp

<div class="sidebar">

    <div class="widget">
        @foreach($global_sidebar_top_ad as $row)
            <div class="ad-sidebar">
                @if($row->sidebar_ad_url == '')
                    <img src="{{ asset('uploads/'.$row->sidebar_ad) }}" alt="">
                @else
                    <a href="{{ $row->sidebar_ad_url }}"><img src="{{ asset('uploads/'.$row->sidebar_ad) }}" alt=""></a>
                @endif
            </div>
        @endforeach
    </div>


    <div class="widget">
        <div class="tag-heading">
            <h2>{{ TAGS }}</h2>
        </div>
        <div class="tag">
            @php
            // get distinct tags that are attached to at least one published post in current language
            $all_tags = \App\Models\Tag::select('tag_name')->distinct()->get();
            @endphp
            @foreach($all_tags as $item)
            @php
                $hasPublished = \App\Models\Tag::where('tag_name', $item->tag_name)
                    ->join('posts','tags.post_id','=','posts.id')
                    ->where('posts.language_id', $current_language_id)
                    ->where('posts.status', 'published')
                    ->exists();
                if(!$hasPublished) continue;
            @endphp
            <div class="tag-item">
                <a href="{{ route('tag_posts_show',$item->tag_name) }}"><span class="badge bg-secondary">{{ $item->tag_name }}</span></a>
            </div>
            @endforeach
        </div>
    </div>

    <div class="widget">
        <div class="archive-heading">
            <h2>{{ ARCHIVE }}</h2>
        </div>
        <div class="archive">
            @php
                $archive_array = [];
                // only published posts for archive
                $all_post_data = \App\Models\Post::where('status','published')->orderBy('id','desc')->get();
                foreach($all_post_data as $row) {
                    $ts = strtotime($row->created_at);
                    $month = date('m',$ts);
                    $month_full = date('F',$ts);
                    $year = date('Y',$ts);
                    $archive_array[] = $month.'-'.$month_full.'-'.$year;
                }
                $archive_array = array_values(array_unique($archive_array));
            @endphp
            <form action="{{ route('archive_show') }}" method="post">
                @csrf
                <select name="archive_month_year" class="form-select" onChange="this.form.submit()">
                    <option value="">{{ SELECT_MONTH }}</option>
                    @for($i=0;$i<count($archive_array);$i++)
                        @php
                            $temp_arr = explode('-',$archive_array[$i]);
                        @endphp
                        <option value="{{ $temp_arr[0].'-'.$temp_arr[2] }}">{{ $temp_arr[1] }}, {{ $temp_arr[2] }}</option>
                    @endfor
                </select>
            </form>
        </div>
    </div>

    <div class="widget">
        @php
        $live_channel_data = \App\Models\LiveChannel::where('language_id',$current_language_id)->get();
        @endphp
        @foreach($live_channel_data as $item)
            <div class="live-channel">
                <div class="live-channel-heading">
                    <h2>{{ $item->heading }}</h2>
                </div>
                <div class="live-channel-item">
                    <iframe width="560" height="315" src="https://www.youtube.com/embed/{{ $item->video_id }}" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </div>
            </div>
        @endforeach        
    </div>
    
    <div class="widget">
        <div class="news">
            <div class="news-heading">
                <h2>{{ POPULAR_RECENT_NEWS }}</h2>
            </div>           

            <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#pills-home" type="button" role="tab" aria-controls="pills-home" aria-selected="true">{{ RECENT_NEWS }}</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pills-profile-tab" data-bs-toggle="pill" data-bs-target="#pills-profile" type="button" role="tab" aria-controls="pills-profile" aria-selected="false">{{ POPULAR_NEWS }}</button>
                </li>
            </ul>
            <div class="tab-content" id="pills-tabContent">
                <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">
                    @php
                    // recent published posts in current language
                    $recent_news_data = \App\Models\Post::with('rSubCategory')
                        ->where('language_id',$current_language_id)
                        ->where('status','published')
                        ->orderBy('id','desc')
                        ->take(10)
                        ->get();
                    @endphp
                    @foreach($recent_news_data as $item)
                        @if($loop->iteration > 5)
                            @break
                        @endif
                        <div class="news-item">
                            <div class="left">
                                <img src="{{ asset('uploads/'.$item->post_photo) }}" alt="">
                            </div>
                            <div class="right">
                                <div class="category">
                                    <span class="badge bg-success">{{ optional($item->rSubCategory)->sub_category_name }}</span>
                                </div>
                                <h2><a href="{{ route('news_detail',$item->id) }}">{{ $item->post_title }}</a></h2>
                                <div class="date-user">
                                    <div class="user">
                                        @if($item->author_id==0)
                                            @php
                                            $user_data = \App\Models\Admin::where('id',$item->admin_id)->first();
                                            @endphp
                                        @else
                                            @php
                                            $user_data = \App\Models\Author::where('id',$item->author_id)->first();
                                            @endphp
                                        @endif
                                        <a href="javascript:void;">{{ $user_data->name ?? '' }}</a>
                                    </div>
                                    <div class="date">
                                        @php
                                        $ts = strtotime($item->updated_at);
                                        $updated_date = date('d F, Y',$ts);
                                        @endphp
                                        <a href="javascript:void;">{{ $updated_date }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="tab-pane fade" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab">
                    @php
                    // popular published posts in current language
                    $popular_news_data = \App\Models\Post::with('rSubCategory')
                        ->where('language_id',$current_language_id)
                        ->where('status','published')
                        ->orderBy('visitors','desc')
                        ->take(10)
                        ->get();
                    @endphp
                    @foreach($popular_news_data as $item)
                        @if($loop->iteration > 5)
                            @break
                        @endif
                        <div class="news-item">
                            <div class="left">
                                <img src="{{ asset('uploads/'.$item->post_photo) }}" alt="">
                            </div>
                            <div class="right">
                                <div class="category">
                                    <span class="badge bg-success">{{ optional($item->rSubCategory)->sub_category_name }}</span>
                                </div>
                                <h2><a href="{{ route('news_detail',$item->id) }}">{{ $item->post_title }}</a></h2>
                                <div class="date-user">
                                    <div class="user">
                                        @if($item->author_id==0)
                                            @php
                                            $user_data = \App\Models\Admin::where('id',$item->admin_id)->first();
                                            @endphp
                                        @else
                                            @php
                                            $user_data = \App\Models\Author::where('id',$item->author_id)->first();
                                            @endphp
                                        @endif
                                        <a href="javascript:void;">{{ $user_data->name ?? '' }}</a>
                                    </div>
                                    <div class="date">
                                        @php
                                        $ts = strtotime($item->updated_at);
                                        $updated_date = date('d F, Y',$ts);
                                        @endphp
                                        <a href="javascript:void;">{{ $updated_date }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach                    
                </div>
            </div>
        </div>
    </div>

    <div class="widget">
        <div class="poll-heading">
            <h2>{{ ONLINE_POLL }}</h2>
        </div>
        <div class="poll">
            @php
                $active_poll = \App\Models\OnlinePoll::with('questions.answers')
                    ->where('language_id', $current_language_id)
                    ->where('expiration', '>', now())
                    ->orderBy('id','desc')
                    ->first();
            @endphp
            @if($active_poll)
                <div class="mb-2">
                    <strong>{{ $active_poll->title }}</strong>
                </div>
                @if(session()->get('poll_votes') && isset(session('poll_votes')[$active_poll->id]) && session('poll_votes')[$active_poll->id] >= $active_poll->voting_limit)
                    <div class="poll-result">
                        @foreach($active_poll->questions as $question)
                            <div class="mb-2">
                                <strong>{{ $question->text }}</strong>
                                <ul class="list-unstyled">
                                    @php $totalVotes = $question->answers->sum('votes'); @endphp
                                    @foreach($question->answers as $answer)
                                        @php $percent = $totalVotes > 0 ? round(($answer->votes / $totalVotes) * 100, 1) : 0; @endphp
                                        <li>
                                            {{ $answer->text }} - {{ $answer->votes }} votes ({{ $percent }}%)
                                            <div class="progress" style="height: 10px;">
                                                <div class="progress-bar" role="progressbar" style="width: {{ $percent }}%;" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                        <a href="{{ route('poll_previous') }}" class="btn btn-primary old" style="margin-top:0;">{{ OLD_RESULTS }}</a>
                    </div>
                @else
                    <form action="{{ route('poll_submit') }}" method="post">
                        @csrf
                        <input type="hidden" name="poll_id" value="{{ $active_poll->id }}">
                        @foreach($active_poll->questions as $question)
                            <div class="mb-2">
                                <strong>{{ $question->text }}</strong>
                                <ul class="list-unstyled">
                                    @foreach($question->answers as $answer)
                                        <li>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="answers[{{ $question->id }}]" id="answer_{{ $answer->id }}" value="{{ $answer->id }}">
                                                <label class="form-check-label" for="answer_{{ $answer->id }}">{{ $answer->text }}</label>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">{{ SUBMIT }}</button>
                            <a href="{{ route('poll_previous') }}" class="btn btn-primary old">{{ OLD_RESULTS }}</a>
                        </div>
                    </form>
                @endif
            @else
                <div class="alert alert-info">No active poll available.</div>
            @endif
        </div>
    </div>

    <div class="widget">
        @foreach($global_sidebar_bottom_ad as $row)
            <div class="ad-sidebar">
                @if($row->sidebar_ad_url == '')
                    <img src="{{ asset('uploads/'.$row->sidebar_ad) }}" alt="">
                @else
                    <a href="{{ $row->sidebar_ad_url }}"><img src="{{ asset('uploads/'.$row->sidebar_ad) }}" alt=""></a>
                @endif
            </div>
        @endforeach
    </div>

</div>
