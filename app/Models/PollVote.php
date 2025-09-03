<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PollVote extends Model
{
    protected $fillable = ['online_poll_id', 'ip_address'];

    public function poll()
    {
        return $this->belongsTo(OnlinePoll::class, 'online_poll_id');
    }
}
