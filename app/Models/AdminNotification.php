<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminNotification extends Model
{
    protected $fillable = [
        'type',
        'message',
        'data',
        'is_read',
        'user_id',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
    ];
}
