<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    public function account_type()
    {
        return $this->belongsTo('App\AccountType');
    }

    public function loans()
    {
        return $this->hasMany('App\Loan');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function associated_members()
    {
        return $this->hasMany('App\AssociatedMember');
    }

    public function Member()
    {
       return $this->belongsTo('App\Member');
    }
}
