<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'firstname', 'lastname', 'username', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function Parent()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

    public function Children()
    {
        return $this->hasMany('App\User', 'id', 'user_id');
    }

    public function accounts()
    {
        return $this->hasMany('App\Account');
    }
}
