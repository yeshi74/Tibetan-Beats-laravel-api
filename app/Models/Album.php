<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Album extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'cover_image'];

    public function songs()
    {
        return $this->hasMany(Song::class);
    }
}
