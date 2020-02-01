<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LoanRepaymentSchedule extends Model
{
    public function loan(){
        return $this->belongsTo('App\Loan');
    }
}
