<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Artist extends Model
{

    protected $table = 'artists';

    protected $fillable = [
        'user_id',
        'display_name',
        'real_name',
        'bio', // optional convenience field
        'country',
        'profile_image',
        'stripe_connect_id',
        'verified',
        'meta',
        'social_links',];

    protected $casts = [
        'meta' => 'array',
        'verified' => 'boolean',
        'social_links' => 'array',
    ];

    // Artist belongs to a user
    // Relationship to user
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    // Optional: an artist can have many songs
    public function songs()
    {
        return $this->hasMany(Song::class, 'artist_id');
    }

        // convenience: get public display name
    public function getPublicNameAttribute()
    {
        return $this->display_name ?? ($this->real_name ?? ($this->user->name ?? null));
    }
}




