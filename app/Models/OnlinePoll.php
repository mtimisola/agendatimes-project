<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnlinePoll extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'expiration', 'visibility', 'voting_limit', 'language_id'
    ];

    public function questions()
    {
        return $this->hasMany(PollQuestion::class);
    }

    public function rLanguage()
    {
        return $this->belongsTo(Language::class, 'language_id');
    }
}
