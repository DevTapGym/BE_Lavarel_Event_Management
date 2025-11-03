<?php

namespace App\Models;

use MongoDB\Laravel\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $connection = 'mongodb';
    protected $collection = 'users';

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public $timestamps = true;
    protected $fillable = [
        'name',
        'email',
        'avatar',
        'is_active',
        'password',
        'phone',
        'roles',
        'email_verified_at',
        'actived_at',
        'current_jti',
        'activation_code',
        'activation_expires_at',
        'last_activation_sent_at',
        'reputation_score',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $attributes = [
        'is_active' => false,
        'current_jti' => null,
        'activation_code' => null,
        'activation_expires_at' => null,
        'last_activation_sent_at' => null,
        'reputation_score' => 70,
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'reputation_score' => 'integer',
    ];


    public function assignRole($roleName)
    {
        $roles = $this->roles ?? [];
        if (!in_array($roleName, $roles)) {
            $roles[] = $roleName;
            $this->roles = $roles;
            $this->save();
        }
    }

    public function hasRole($roleName)
    {
        return in_array($roleName, $this->roles ?? []);
    }
}
