<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'registrations';

    protected $fillable = [
        'user_id',
        'event_id',
        'registration_at',
        'cancelled_at',
        'queue_order',
        'status',
        'cancel_reason',
    ];

    protected $casts = [
        'registration_date' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'WAITING_CONFIRM', // CONFIRMED', 'CANCELLED', 'WAITING', 'NO_SHOW'
    ];
}
