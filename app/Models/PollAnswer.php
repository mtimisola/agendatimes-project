<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PollAnswer extends Model
{
    protected $fillable = ['poll_question_id', 'text', 'votes'];

    public function question()
    {
        return $this->belongsTo(PollQuestion::class, 'poll_question_id');
    }
}
