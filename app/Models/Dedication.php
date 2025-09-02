<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dedication extends Model
{
    protected $fillable = [
        'user_id',
        'song_id',
        'to_name',
        'message',
        // keep this in fillable; if the column doesn't exist the controller won't send it
        'to_user_id',
    ];

    protected $appends = ['cover_image_url'];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function song()
    {
        return $this->belongsTo(\App\Models\Song::class);
    }

    // receiver (optional)
    public function toUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'to_user_id');
    }

    public function getCoverImageUrlAttribute()
    {
        if ($this->song) {
            return $this->song->cover_image_url; // ✅ proxies Song accessor
        }
        return null;
    }

    public function likes()
    {
        return $this->hasMany(DedicationLike::class);
    }

    public function likedBy(User $user)
    {
        return $this->likes()->where('user_id', $user->id)->exists();
    }

    

}
