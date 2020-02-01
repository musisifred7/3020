<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CashAtHand extends Model
{
    // This is to allow massive assignment or bulk insertion
    protected $fillable = ['processing_date', 'type', 'amount', 'balance', 'total_capital', 'user_id'];
}