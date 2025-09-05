<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;

class AdminNotification extends Model
{
    protected $table = 'admin_notifications';

    protected $fillable = [
        'title', 'body', 'type', 'payload',
        'audience', 'sent_count', 'attempts',
        'scheduled_at', 'sent_at', 'created_by'
    ];

    protected $casts = [
        'payload' => AsArrayObject::class,
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
