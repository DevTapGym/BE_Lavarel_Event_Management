<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'feedbacks';
    protected $fillable = [
        'registration_id',
        'event_id',
        'rating',
        'comments',
    ];
    public $timestamps = true;
}
