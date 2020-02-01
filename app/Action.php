<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Action extends Model
{
    public function chargetype()
    {
        return $this->hasMany('App\chargeType');
    }
}
