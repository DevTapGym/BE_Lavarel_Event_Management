<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'locations';

    protected $fillable = [
        'name',
        'building',
        'address',
        'capacity',
    ];
    public $timestamps = true;
}
