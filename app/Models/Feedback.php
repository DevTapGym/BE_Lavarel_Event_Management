<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Feedback extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'feedbacks';
    protected $fillable = [
        'registration_id',
        'event_id',
        'rating',
        'comments',
        'is_hidden'
    ];
    public $timestamps = true;

    public function registration()
    {
        return $this->belongsTo(Registration::class, 'registration_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }
}
