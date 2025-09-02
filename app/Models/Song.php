<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;

    class Song extends Model
    {
        use HasFactory;

        protected $fillable = ['title', 'artist', 'file_path', 'cover_image', 'album_id', 'genre', 'play_count'];

        protected $appends = ['audio_url', 'cover_image_url'];

        public function album()
        {
            return $this->belongsTo(Album::class);
        }

        public function getAudioUrlAttribute() // ✅ FIXED
        {
            return asset('storage/' . $this->file_path);
        }

        public function likes()
        {
            return $this->belongsToMany(User::class, 'song_user_likes'); 
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



