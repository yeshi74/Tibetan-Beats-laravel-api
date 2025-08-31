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

        public function getCoverImageUrlAttribute()
        {
            return asset('storage/' . $this->cover_image);
        }

        public function likes()
        {
            return $this->belongsToMany(User::class, 'song_user_likes'); 
        }

    }



