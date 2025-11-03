<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;


class Event extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'events';

    protected $fillable = [
        'title',
        'description',
        'location_id',
        'start_date',
        'end_date',
        'organizer',
        'topic',
        'capacity',
        'waiting_capacity',
        'status',
        'image_url',
        'approval_status',
        'current_confirmed',
        'current_waiting',
    ];
    public $timestamps = true;

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'capacity' => 'integer',
    ];

    protected $attributes = [
        'status' => 'PENDING', //PENDING', 'UPCOMING', 'OPEN', 'ONGOING', 'ENDED', 'CANCELLED'
        'approval_status' => 'WAITING', //WAITING', 'APPROVED', 'REJECTED'
        'current_confirmed' => 0,
        'current_waiting' => 0,
    ];
}
