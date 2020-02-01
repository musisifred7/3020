<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AccountType extends Model
{

    public function charge_types()
    {
        return $this->hasMany('App\chargeType');
    }

    public function accounts()
    {
        return $this->hasMany('App\Account');
    }
}