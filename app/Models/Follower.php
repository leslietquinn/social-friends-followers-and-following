<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Follower extends Model
{
    use HasFactory;

    protected $table='followers';
    protected $primaryKey='id';
    protected $perPage=12;
    
    protected $fillable=[
        'user_id'
      , 'follower'
    ];

    protected $hidden=[];
    protected $casts=[];

    /**
     * Return those Users (ie, B, C and D) that are being followed by another User (ie, A)
     */

    public function following()
    {
        return $this->belongsTo(User::class, 'follower');
    }

    /**
     * Return those Users (ie, B, C and D) who are themselves, following another User (ie, A)
     */
    
    public function followers()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
