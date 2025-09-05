<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;

class InAppMessage extends Model
{
    protected $fillable = [
        'key','type','title','body','image_url','cta','audience',
        'priority','start_at','end_at','max_impressions_per_user',
        'max_impressions_global','dismissible','dismiss_persists',
        'status','metadata','created_by'
    ];

    protected $casts = [
        'title' => AsArrayObject::class,
        'body'  => AsArrayObject::class,
        'cta'   => AsArrayObject::class,
        'metadata' => AsArrayObject::class,
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    // scopes
    public function scopeActive($q)
    {
        $now = now();
        return $q->where('status','active')
                 ->where(function($s) use ($now) {
                     $s->whereNull('start_at')->orWhere('start_at','<=',$now);
                 })->where(function($s) use ($now) {
                     $s->whereNull('end_at')->orWhere('end_at','>=',$now);
                 });
    }

    public function views()
    {
        return $this->hasMany(InAppMessageView::class, 'in_app_message_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
