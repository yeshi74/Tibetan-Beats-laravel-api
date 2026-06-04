<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Notifications\CustomVerifyEmail;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'mobile',
        'otp_code',
        'otp_expires_at',
        'profile_image',
        'role_id', // <- allow updating role via role_id
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_premium' => 'boolean',
        'premium_ends_at' => 'datetime',
        'premium_expires_at' => 'datetime',
        // 'password' => 'hashed', // if you use this feature, make sure framework supports it
    ];

    /**
     * Hidden attributes for arrays / JSON.
     *
     * @var array<int,string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = ['profile_image_url'];

    // ---------------- Relations & helpers ----------------

    /**
     * Role relation (uses role_id foreign key)
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Check user has a role by name.
     */
    public function hasRole(string $name): bool
    {
        return $this->role?->name === $name;
    }

    /**
     * Check any role from list.
     */
    public function hasAnyRole(array $names): bool
    {
        return in_array($this->role?->name, $names, true);
    }

    /**
     * Send custom email verification notification.
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new CustomVerifyEmail);
    }

    // User playlists
    public function playlists()
    {
        return $this->hasMany(\App\Models\Playlist::class);
    }

    // Songs the user liked
    public function likedSongs(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Song::class, 'liked_songs')
                    ->withTimestamps();
    }

    // Dedication likes
    public function likedDedications(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Dedication::class, 'dedication_likes')
                    ->withTimestamps();
    }

    // ---------------- Accessors ----------------

    public function getProfileImageUrlAttribute(): ?string
    {
        if (!$this->profile_image) {
            return null;
        }

        $val = $this->profile_image;

        // If already an absolute URL
        if (str_starts_with($val, 'http://') || str_starts_with($val, 'https://')) {
            return $val;
        }

        return asset('storage/' . ltrim($val, '/'));
    }

    public function artist()
    {
        return $this->hasOne(\App\Models\Artist::class,'user_id','id');
    }

    public function followedArtists()
    {
        return $this->hasMany(
            ArtistFollower::class,
            'user_id'
        );
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }


}
