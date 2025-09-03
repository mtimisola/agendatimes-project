@extends('front.layout.app')

@section('heading', 'Past Poll Results')

@section('main_content')
<div class="container py-4">
    <h2 class="mb-4">Past Poll Results</h2>
    <div class="row">
        @forelse($past_pools as $pool)
        <div class="col-md-12 mb-4">
            <div class="card shadow h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">{{ $pool->title }}</h5>
                </div>
                <div class="card-body">
                    @php
                        $userVotes = session('poll_votes', []);
                        $isActive = $pool->expiration && \Carbon\Carbon::parse($pool->expiration)->isFuture();
                        $canVote = $isActive && (!isset($userVotes[$pool->id]) || $userVotes[$pool->id] < $pool->voting_limit);
                    @endphp
                    @foreach($pool->questions as $question)
                        <div class="mb-4">
                            <div class="poll-question py-2 px-3 mb-3" style="background:#f8f9fa;border-radius:6px;border-left:4px solid #0d6efd;">
                                <span class="fw-bold text-primary" style="font-size:1.1rem;">{{ $question->text }}</span>
                            </div>
                            @if($canVote)
                                <form action="{{ route('poll_submit') }}" method="post" class="mb-3">
                                    @csrf
                                    <input type="hidden" name="poll_id" value="{{ $pool->id }}">
                                    <div class="ms-3">
                                        @foreach($question->answers as $answer)
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="radio" name="answers[{{ $question->id }}]" id="answer_{{ $answer->id }}_{{ $pool->id }}" value="{{ $answer->id }}">
                                                <label class="form-check-label" for="answer_{{ $answer->id }}_{{ $pool->id }}">{{ $answer->text }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm mt-2">Vote</button>
                                </form>
                            @endif
                            @foreach($question->answers as $answer)
                                <div class="mb-4 ms-3">
                                    <span class="fw-semibold text-dark">{{ $answer->text }}</span>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar" role="progressbar" style="width: {{ $answer['percent'] ?? 0 }}%;" aria-valuenow="{{ $answer['percent'] ?? 0 }}" aria-valuemin="0" aria-valuemax="100">
                                            {{ $answer->votes }} votes ({{ $answer['percent'] ?? 0 }}%)
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
                <div class="card-footer text-muted small">
                    @if($pool->expiration && \Carbon\Carbon::parse($pool->expiration)->isFuture())
                        <span class="text-success">Active</span> &ndash; Expires: {{ \Carbon\Carbon::parse($pool->expiration)->format('M d, Y') }}
                    @else
                        <span class="text-danger">Expired</span> @if($pool->expiration) &ndash; {{ \Carbon\Carbon::parse($pool->expiration)->format('M d, Y') }} @endif
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-info">No past polls found.</div>
        </div>
        @endforelse
    </div>
</div>
@endsection
