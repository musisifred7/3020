<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ChargeType extends Model
{
    public function Action()
    {
        return $this->belongsTo('App\Action');
    }

    public function account_type()
    {
        return $this->belongsTo('App\Accounttype');
    }
}
