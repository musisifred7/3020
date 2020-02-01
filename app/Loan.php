<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    public function loan_product()
    {
        return $this->belongsTo('App\LoanProduct');
    }

    public function account()
    {
        return $this->belongsTo('App\Account');
    }

    public function loan_repayment_schedules()
    {
        return $this->hasMany('App\LoanRepaymentSchedule');
    }
}
