<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;

class Song extends Model
{
    use HasFactory;

    protected $fillable = [
        'album_id',
        'title',
        'artist',       // human-friendly string (legacy)
        'artist_id',    // newly added relation
        'play_count',
        'file_path',
        'cover_image',
        'cover_image_url',
        'genre_id',
        'status',
        'is_published',
    ];

    protected $appends = ['audio_url', 'cover_image_url'];

    protected $casts = [
        'play_count' => 'integer',
        'is_published' => 'boolean',
    ];

    public function artistRecord()
    {
        // points to Artist model (if you keep artists table)
        return $this->belongsTo(Artist::class, 'artist_id');
    }


    public function album()
    {
        return $this->belongsTo(Album::class);
    }

    public function likes()
    {
        return $this->belongsToMany(User::class, 'song_user_likes'); 
    }

    public function genre()
    {
        return $this->belongsTo(Genre::class, 'genre_id');
    }

    public function getAudioUrlAttribute()
    {
        return asset('storage/' . $this->file_path);
    }

    public function getCoverImageUrlAttribute()
    {
        if (!$this->cover_image) {
            return null;
        }

        if (str_starts_with($this->cover_image, 'http://') || str_starts_with($this->cover_image, 'https://')) {
            return $this->cover_image;
        }

        return asset('storage/' . ltrim($this->cover_image, '/'));
    }
}




