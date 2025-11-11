<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Notification extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'notifications';

    protected $fillable = [
        'event_id',
        'organizer_id',
        'message',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
