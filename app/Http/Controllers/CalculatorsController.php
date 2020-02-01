<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class CalculatorsController extends Controller
{
    public function shares()
    {
    	$sharevalue = DB::SELECT('SELECT * FROM tbl_share_values order by id limit 1');

    	// return $sharevalue;
    	return view('calculators.shares')->with('sharevalue',$sharevalue);
    }
}
