<?php

namespace App\Models;

use App\Models\Follower;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table='users';
    protected $primaryKey='id';
    protected $perPage=12;
    
    protected $fillable = [
        'name'
      , 'email'
      , 'username'
      , 'password'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Return those Users (ie, B, C and D) that are being followed by another User (ie, A)
     */

    public function following()
    {
        return $this->hasMany(Follower::class);
    }
    
    /**
     * Return those Users (ie, B, C and D) who are themselves, following another User (ie, A)
     */

    public function followers()
    {
        return $this->hasMany(Follower::class, 'follower');
    }

}
