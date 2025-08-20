<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Playlist extends Model
{
    protected $fillable = ['user_id', 'name', 'description'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function songs()
    {
        return $this->belongsToMany(
            Song::class,      // Related model
            'playlist_song',  // Pivot table
            'playlist_id',    // Foreign key on pivot for this model
            'song_id'         // Foreign key on pivot for related model
        )->withTimestamps();
    }
}
