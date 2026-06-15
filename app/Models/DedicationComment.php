<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DedicationComment extends Model
{
    protected $fillable = [
        'dedication_id',
        'user_id',
        'comment',
    ];

    public function dedication()
    {
        return $this->belongsTo(Dedication::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}