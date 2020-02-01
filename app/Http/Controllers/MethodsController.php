<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class MethodsController extends Controller
{
    public function cash_at_hand(){
    	$cash_at_hand = DB::SELECT('SELECT * FROM tbl_cash_at_hands order by id desc limit 1');

    	return $cash_at_hand[0]->balance;
    }
}
