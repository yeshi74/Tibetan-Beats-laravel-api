<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SongReport extends Model
{
    protected $fillable = [
        'user_id',
        'song_id',
        'reason',
        'details',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function song()
    {
        return $this->belongsTo(Song::class);
    }
}