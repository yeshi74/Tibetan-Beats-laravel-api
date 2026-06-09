<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SongClaim extends Model
{
    protected $fillable = [
        'user_id',
        'song_id',
        'artist_id',
        'song_title',
        'claimant_name',
        'email',
        'reason',
        'status',
        'admin_notes',
    ];

    public function song()
    {
        return $this->belongsTo(\App\Models\Song::class);
    }

    public function artist()
    {
        return $this->belongsTo(\App\Models\Artist::class);
    }

}
