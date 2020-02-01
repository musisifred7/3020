<?php

namespace App\Http\Controllers;
use DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $summary = DB::SELECT("SELECT count(*) as sum_accounts,(SELECT count(*) FROM tbl_members) as sum_members,(SELECT count(*) FROM tbl_loans) as sum_loans, (SELECT count(*) FROM tbl_users) as sum_users FROM tbl_accounts");

        // return $summary;
        return view('home',compact('summary'));
    }
}