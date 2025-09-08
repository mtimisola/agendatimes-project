@extends('front.layout.app')

@section('main_content')
@section('meta_tags')
    @if(isset($all_polls) && count($all_polls))
        @php $firstPoll = $all_polls[0]; @endphp
        <meta property="og:title" content="{{ $firstPoll->title ?? $firstPoll->question }} - Agenda Times Poll" />
        <meta property="og:description" content="Vote now: {{ $firstPoll->title ?? $firstPoll->question }}" />
        <meta property="og:type" content="website" />
        <meta property="og:url" content="{{ request()->fullUrl() }}" />
        <meta property="og:image" content="{{ asset('uploads/poll_default.jpg') }}" />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content="{{ $firstPoll->title ?? $firstPoll->question }} - Agenda Times Poll" />
        <meta name="twitter:description" content="Vote now: {{ $firstPoll->title ?? $firstPoll->question }}" />
        <meta name="twitter:image" content="{{ asset('uploads/poll_default.jpg') }}" />
    @endif
@endsection
<div class="page-top">
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .poll-card { background: #fff; border: 1px solid #ddd; border-radius: 10px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 6px rgba(0,0,0,0.08); max-width: 600px; margin-left: auto; margin-right: auto; }
        .poll-card.inactive { opacity: 0.7; }
        .poll-title { font-size: 20px; font-weight: bold; margin-bottom: 8px; color: #222; }
        .poll-status { font-size: 14px; color: #888; margin-bottom: 15px; }
        .poll-option { margin-bottom: 10px; }
        .vote-btn { padding: 10px 20px; background: #008080; color: #fff; border: none; border-radius: 5px; cursor: pointer; font-size: 15px; width: 100%; }
        .vote-btn:hover { background: #006666; }
        .poll-results { margin-top: 10px; }
        .result-option { display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 14px; }
        .poll-share { margin-top: 15px; font-size: 14px; }
        .poll-share a { margin-right: 10px; color: #008080; text-decoration: none; }
        .poll-share a:hover { text-decoration: underline; }
        @media (max-width: 600px) {
            .poll-card { padding: 12px; }
            .poll-title { font-size: 17px; }
            .vote-btn { font-size: 14px; padding: 8px 10px; }
        }
    </style>
    <div class="container">
        <div class="row">
            <div class="col-lg-8 col-md-6">
                <h3 class="mb-3">All Polls</h3>
                @forelse($all_polls as $poll)
                    <div class="poll-card @if(!$poll->is_active) inactive @endif">
                        <div class="poll-title">{{ $poll->title ?? $poll->question }}</div>
                        <div class="poll-status">
                            @if($poll->is_active)
                                Ends: {{ $poll->end_date }}
                            @else
                                Closed on: {{ $poll->end_date }}
                            @endif
                        </div>
                        @php $userVotes = session()->get('poll_votes', []); @endphp
                        @foreach($poll->questions as $question)
                            <div class="mb-2 fw-bold">{{ $question->text }}</div>
                            @if($poll->can_vote && (!isset($userVotes[$poll->id])))
                                @if(session('error'))
                                    <div class="poll-results">
                                        @foreach($question->answers as $answer)
                                            <div class="result-option">
                                                <span>{{ $answer->text }}</span>
                                                <span>{{ $answer['percent'] ?? 0 }}%</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <form action="{{ route('poll_submit') }}" method="post" class="mb-3">
                                        @csrf
                                        <input type="hidden" name="poll_id" value="{{ $poll->id }}">
                                        @foreach($question->answers as $answer)
                                            <div class="poll-option">
                                                <input class="form-check-input" type="radio" name="answers[{{ $question->id }}]" id="answer_{{ $answer->id }}_{{ $poll->id }}" value="{{ $answer->id }}">
                                                <label class="form-check-label" for="answer_{{ $answer->id }}_{{ $poll->id }}">{{ $answer->text }}</label>
                                            </div>
                                        @endforeach
                                        <button type="submit" class="vote-btn">Vote</button>
                                    </form>
                                @endif
                            @else
                                <div class="poll-results">
                                    @foreach($question->answers as $answer)
                                        <div class="result-option">
                                            <span>{{ $answer->text }}</span>
                                            <span>{{ $answer['percent'] ?? 0 }}%</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @endforeach
                        <div class="poll-share">
                            Share:
                            <a href="#" onclick="window.open('https://twitter.com/intent/tweet?text={{ urlencode($poll->title ?? $poll->question) }}','_blank')">Twitter</a> |
                            <a href="#" onclick="window.open('https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->fullUrl()) }}','_blank')">Facebook</a>
                        </div>
                    </div>
                @empty
                    <div class="alert alert-info">No polls found.</div>
                @endforelse
            </div>
            <div class="col-lg-4 col-md-6">
                @include('front.layout.sidebar')
            </div>
        </div>
    </div>
// ...existing code...
    </div>
</div>
@endsection