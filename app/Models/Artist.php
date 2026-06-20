<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; // <-- add this
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Artist extends Model
{
    protected $table = 'artists';

    protected $fillable = [
        'user_id',
        'display_name',
        'real_name',
        'bio',
        'country',
        'profile_image',
        'stripe_connect_id',
        'verified',
        'meta',
        'social_links',
        'show_profile',
    ];

    protected $casts = [
        'meta' => 'array',
        'verified' => 'boolean',
        'social_links' => 'array',
    ];

    // Artist belongs to a user
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    // an artist can have many songs
    public function songs(): HasMany
    {
        return $this->hasMany(\App\Models\Song::class, 'artist_id', 'id')
                    ->orderByDesc('created_at');
    }

    // convenience: get public display name
    public function getPublicNameAttribute()
    {
        return $this->display_name ?? ($this->real_name ?? ($this->user->name ?? null));
    }

    public function followers()
    {
        return $this->hasMany(ArtistFollower::class,'artist_id');
    }

}
