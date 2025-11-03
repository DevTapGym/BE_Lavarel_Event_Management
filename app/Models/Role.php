<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Role extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'roles';

    protected $fillable = [
        'name',
        'description',
        'permissions'
    ];

    protected $casts = [
        'permissions' => 'array',
    ];

    public $timestamps = true;
}
