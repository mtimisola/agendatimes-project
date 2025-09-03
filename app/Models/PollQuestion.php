<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PollQuestion extends Model
{
    protected $fillable = ['online_poll_id', 'text'];

    public function poll()
    {
        return $this->belongsTo(OnlinePoll::class, 'online_poll_id');
    }
    public function answers()
    {
        return $this->hasMany(PollAnswer::class);
    }
}
