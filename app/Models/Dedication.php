<?php

// app/Models/Dedication.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dedication extends Model
{
    protected $fillable = ['user_id','song_id','to_name','message','is_public'];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function song(): BelongsTo {
        return $this->belongsTo(Song::class);
    }
}

