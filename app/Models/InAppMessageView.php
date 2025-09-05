<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InAppMessageView extends Model
{
    protected $fillable = [
        'in_app_message_id','user_id','device_id','impressions','last_shown_at','dismissed_at'
    ];

    protected $casts = [
        'last_shown_at' => 'datetime',
        'dismissed_at' => 'datetime',
    ];

    public function message()
    {
        return $this->belongsTo(InAppMessage::class, 'in_app_message_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
