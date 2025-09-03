<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OnlinePoll;
use App\Models\Language;
use App\Helper\Helpers;
use App\Models\PollVote;

class PollController extends Controller
{
    public function submit(Request $request)
    {
        $request->validate([
            'poll_id' => 'required|integer|exists:online_polls,id',
            'answers' => 'required|array',
            'answers.*' => 'required|integer|exists:poll_answers,id',
        ]);

        $poll = OnlinePoll::with('questions.answers')->findOrFail($request->poll_id);

        // Get user IP
        $ip = $request->ip();
        // Check if this IP has already voted for this poll
        if (PollVote::where('online_poll_id', $poll->id)->where('ip_address', $ip)->exists()) {
            return redirect()->back()->with('error', 'You have already voted for this poll.');
        }

        // Voting limit enforcement (session-based, optional)
        $userVotes = session()->get('poll_votes', []);
        if (isset($userVotes[$poll->id]) && $userVotes[$poll->id] >= $poll->voting_limit) {
            return redirect()->back()->with('error', 'You have reached the voting limit for this poll.');
        }

        foreach ($request->answers as $answerId) {
            $answer = \App\Models\PollAnswer::find($answerId);
            if ($answer) {
                $answer->increment('votes');
            }
        }

        // Store IP in poll_votes table
        PollVote::create([
            'online_poll_id' => $poll->id,
            'ip_address' => $ip,
        ]);

        // Track user votes in session
        $userVotes[$poll->id] = isset($userVotes[$poll->id]) ? $userVotes[$poll->id] + 1 : 1;
        session()->put('poll_votes', $userVotes);

        return redirect()->back()->with('success', 'Your vote is counted successfully');
    }

    public function previous()
    {
        Helpers::read_json();

        if(!session()->get('session_short_name')) {
            $current_short_name = Language::where('is_default','Yes')->first()->short_name;
        } else {
            $current_short_name = session()->get('session_short_name');
        }
        $current_language_id = Language::where('short_name',$current_short_name)->first()->id;

        $past_pools = OnlinePoll::with('questions.answers')
            ->where('language_id', $current_language_id)
            ->orderBy('id', 'desc')
            ->get();

        // Prepare results for chart display
        foreach ($past_pools as $pool) {
            foreach ($pool->questions as $question) {
                $totalVotes = $question->answers->sum('votes');
                foreach ($question->answers as $answer) {
                    $answer['percent'] = $totalVotes > 0 ? round(($answer->votes / $totalVotes) * 100, 1) : 0;
                }
            }
        }

        return view('front.pool_previous', compact('past_pools'));
    }
}
