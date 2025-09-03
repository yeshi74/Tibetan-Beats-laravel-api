<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Playlist extends Model
{
    protected $fillable = ['name',
        'description',
        'cover_image',
        'is_featured',
        'type',
        'user_id'];

        protected $casts = [
            'is_featured' => 'boolean',
        ];

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
