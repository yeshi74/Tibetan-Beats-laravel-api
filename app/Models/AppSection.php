<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Genre;
use App\Models\Playlist;

class AppSection extends Model
{
    protected $fillable = [
        'page',
        'title',
        'section_type',
        'reference_id',
        'sort_order',
        'is_active',
    ];

    public function genre()
    {
        return $this->belongsTo(Genre::class, 'reference_id');
    }

    public function playlist()
    {
        return $this->belongsTo(Playlist::class, 'reference_id');
    }


}