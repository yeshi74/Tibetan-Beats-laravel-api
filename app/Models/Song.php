<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;

class Song extends Model
{
    use HasFactory;

    protected $table = 'songs';

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
        'created_at', 'updated_at'
    ];

    protected $appends = ['audio_url', 'cover_image_url'];

    protected $casts = [
        'play_count' => 'integer',
        'is_published' => 'boolean',
    ];

        /**
     * Scope to return only published songs.
     */
    public function scopePublished(Builder $q): Builder
    {
        return $q->where('status', 'published');
    }

    /**
     * Return whether given user (or null) can view this song even if not published.
     * e.g. artist owner or admin.
     */
    public function isViewableBy($user = null): bool
    {
        if ($this->status === 'published') return true;
        if (!$user) return false;
        // assume user has 'is_admin' boolean or role property — adapt if different
        if (property_exists($user, 'is_admin') && $user->is_admin) return true;
        if (method_exists($user, 'hasRole') && $user->hasRole('admin')) return true;
        return (int) $this->artist_id === (int) ($user->id ?? 0);
    }

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




