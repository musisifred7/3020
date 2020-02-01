<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LoanProduct extends Model
{
    public function loans()
    {
        return $this->hasMany('App\Loan');
    }
}
