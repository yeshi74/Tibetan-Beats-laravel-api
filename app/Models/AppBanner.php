<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppBanner extends Model
{
    use HasFactory;

    protected $table = 'app_banners'; // ✅ your table name

    protected $fillable = [
        'title',
        'subtitle',
        'image_url',
        'cta_type',  // 'song', 'playlist', 'album', 'url'
        'cta_id',    // ID for song/playlist/album
        'cta_url',   // external link
        'is_active', // boolean
    ];

    // Example relationships if needed:
    public function song()
    {
        return $this->belongsTo(Song::class, 'cta_id');
    }

    public function playlist()
    {
        return $this->belongsTo(Playlist::class, 'cta_id');
    }

    public function album()
    {
        return $this->belongsTo(Album::class, 'cta_id');
    }
}
