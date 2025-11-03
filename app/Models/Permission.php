<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Permission extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'permissions';

    protected $fillable = [
        'name',
        'module',
        'method',
        'api_path',
        'api',
    ];

    public $timestamps = true;
}
