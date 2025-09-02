<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\CustomVerifyEmail;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'mobile',
        'otp_code',
        'otp_expires_at','profile_image'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_premium' => 'boolean',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = ['profile_image_url'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    

    public function sendEmailVerificationNotification()
    {
        $this->notify(new CustomVerifyEmail);
    }

    public function playlists()
    {
        return $this->hasMany(\App\Models\Playlist::class);
    }

    public function likedSongs() {
    return $this->belongsToMany(\App\Models\Song::class, 'liked_songs')
                ->withTimestamps();
    }

    // app/Models/Song.php
    public function likedBy() {
        return $this->belongsToMany(\App\Models\User::class, 'liked_songs')
                    ->withTimestamps();
    }

    public function getProfileImageUrlAttribute()
    {
        if (!$this->profile_image) {
            return null;
        }

        // Already an absolute URL
        if (str_starts_with($this->profile_image, 'http://') || str_starts_with($this->profile_image, 'https://')) {
            return $this->profile_image;
        }

        // ✅ Otherwise build from storage
        return asset('storage/' . ltrim($this->profile_image, '/'));
    }


    public function likedDedications()
    {
        return $this->belongsToMany(Dedication::class, 'dedication_likes')
                    ->withTimestamps();
    }


}
