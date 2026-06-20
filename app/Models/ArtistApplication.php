<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArtistApplication extends Model
{
    protected $fillable = [
        'user_id',
        'display_name',
        'real_name',
        'country',
        'profile_image',
        'social_links',
        'meta',
        'status',
        'admin_notes',
    ];

    protected $casts = [
        'social_links' => 'array',
        'meta' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
}
