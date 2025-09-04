<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title','image_path','cta_type','cta_id','cta_url',
        'is_active','position','starts_at','ends_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
    ];

    // Public URL for the image
    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? asset('storage/'.$this->image_path) : null;
    }

    // Show only currently active banners
    public function scopeCurrentlyActive($q)
    {
        $now = now();
        return $q->where('is_active', true)
                 ->where(function ($qq) use ($now) {
                     $qq->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
                 })
                 ->where(function ($qq) use ($now) {
                     $qq->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
                 });
    }
}
