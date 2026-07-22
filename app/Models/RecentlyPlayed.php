<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecentlyPlayed extends Model
{
    protected $table = 'recently_played';

    protected $fillable = [
        'user_id',
        'song_id',
    ];

    public function song()
    {
        return $this->belongsTo(Song::class,'song_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }



}
