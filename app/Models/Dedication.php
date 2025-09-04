<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;   // ✅ correct Builder
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dedication extends Model
{
    protected $fillable = [
        'user_id',
        'song_id',
        'to_name',
        'message',          // you’re storing the text in `message`
        'to_user_id',
        'status',
        'reviewed_by',
        'reviewed_at',
        'reject_reason',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    protected $appends = ['cover_image_url'];

    /* ----- Scopes ----- */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }
    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', 'rejected');
    }

    /* ----- Relations ----- */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function song(): BelongsTo
    {
        return $this->belongsTo(Song::class);
    }
    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    /* ----- Accessors ----- */
    public function getCoverImageUrlAttribute(): ?string
    {
        return $this->song ? $this->song->cover_image_url : null;
    }

    // Optional: alias `text` -> `message` so existing blades using $d->text still work
    public function getTextAttribute(): ?string
    {
        return $this->message;
    }

    /* ----- Likes ----- */
    public function likes()
    {
        return $this->hasMany(DedicationLike::class);
    }
    public function likedBy(User $user): bool
    {
        return $this->likes()->where('user_id', $user->id)->exists();
    }
}
