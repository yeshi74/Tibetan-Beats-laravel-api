<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Mood extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'emoji',
        'description',
        'sort_order',
        'is_active',
        'start_color',
        'end_color',
    ];

    protected static function booted()
    {
        static::creating(function ($mood) {
            if (empty($mood->slug)) {
                $mood->slug = Str::slug($mood->name);
            }
        });
    }

    public function songs()
    {
        return $this->belongsToMany(Song::class)
            ->withTimestamps();
    }
}