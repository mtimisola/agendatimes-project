<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OnlinePoll;

class AdminOnlinePollController extends Controller
{
    public function show()
    {
        $online_poll_data = OnlinePoll::orderBy('id','desc')->get();
    $active_polls = OnlinePoll::where('expiration', '>', now())->orderBy('id','desc')->get();
    return view('admin.online_poll_show', compact('online_poll_data', 'active_polls'));
    }

    public function create()
    {
        return view('admin.online_poll_create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'expiration' => 'nullable|date',
            'visibility' => 'required|in:public,private',
            'voting_limit' => 'required|integer|min:1',
            'language_id' => 'required|integer',
            'questions' => 'required|array|min:1',
            'questions.*.text' => 'required|string|max:255',
            'questions.*.answers' => 'required|array|min:2',
        ]);

        $online_poll = OnlinePoll::create([
                'title' => $request->title,
                'expiration' => $request->expiration ? \Carbon\Carbon::parse($request->expiration)->setTime(23,59,59) : null,
            'visibility' => $request->visibility,
            'voting_limit' => $request->voting_limit,
            'language_id' => $request->language_id,
        ]);

        foreach ($request->questions as $questionData) {
            $question = $online_poll->questions()->create([
                'text' => $questionData['text'],
            ]);
            // Only save non-empty answers
            $validAnswers = array_filter($questionData['answers'], function($a) {
                return !empty(trim($a));
            });
            foreach ($validAnswers as $answerText) {
                $question->answers()->create([
                    'text' => $answerText,
                    'votes' => 0,
                ]);
            }
        }

        return redirect()->route('admin_online_poll_show')->with('success', 'Poll created successfully.');
    }

    public function edit($id)
    {
        $online_poll_data = OnlinePoll::with('questions.answers')->findOrFail($id);
        return view('admin.online_poll_edit', compact('online_poll_data'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'expiration' => 'nullable|date',
            'visibility' => 'required|in:public,private',
            'voting_limit' => 'required|integer|min:1',
            'language_id' => 'required|integer',
            'questions' => 'required|array|min:1',
            'questions.*.text' => 'required|string|max:255',
            'questions.*.answers' => 'required|array|min:2',
        ]);

        $online_poll = OnlinePoll::findOrFail($id);
        $online_poll->update([
            'title' => $request->title,
            'expiration' => $request->expiration,
            'visibility' => $request->visibility,
            'voting_limit' => $request->voting_limit,
            'language_id' => $request->language_id,
        ]);

        // Remove old questions/answers
        foreach ($online_poll->questions as $question) {
            $question->answers()->delete();
            $question->delete();
        }

        // Add new questions/answers
        foreach ($request->questions as $questionData) {
            $question = $online_poll->questions()->create([
                'text' => $questionData['text'],
            ]);
            // Only save non-empty answers
            $validAnswers = array_filter($questionData['answers'], function($a) {
                return !empty(trim($a));
            });
            foreach ($validAnswers as $answerText) {
                $question->answers()->create([
                    'text' => $answerText,
                    'votes' => 0,
                ]);
            }
        }

        return redirect()->route('admin_online_poll_show')->with('success', 'Poll updated successfully.');
    }

    public function delete($id)
    {
        $online_poll_data = OnlinePoll::where('id',$id)->first();
        $online_poll_data->delete();

        return redirect()->route('admin_online_poll_show')->with('success', 'Data is deleted successfully.');

    }
}
