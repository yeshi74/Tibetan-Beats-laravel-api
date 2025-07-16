<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Song extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'artist', 'file_path', 'cover_image', 'album_id'];

    protected $appends = ['url'];

    public function album()
    {
        return $this->belongsTo(Album::class);
    }

    public function getUrlAttribute()
    {
        return asset('storage/' . $this->file_path);
    }
}

