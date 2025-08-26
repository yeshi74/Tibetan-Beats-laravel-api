<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DedicationReaction extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $fillable = [
        'dedication_id',
        'user_id',
        'type', // heart|pray|om|lamp|flag|like|custom
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
