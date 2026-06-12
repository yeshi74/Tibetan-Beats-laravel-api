<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArtistClaim extends Model
{
    protected $fillable = [
        'user_id',
        'artist_id',
        'claimant_name',
        'email',
        'reason',
        'status',
        'admin_notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function artist()
    {
        return $this->belongsTo(Artist::class);
    }
}