<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class ReportsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function general(Request $request){
        $status = 'failed';
        $report = 'Unable to get report';

        $report_type = $request->input('report_type');
        $report_to = $request->input('report_to');
        $report_from = $request->input('report_from');

        if ($report_type == 'account creation') {
            $status = 'success';
            $report = DB::SELECT("SELECT * FROM tbl_accounts where created_at >= '$report_from' AND created_at <= '$report_to'");
        }elseif ($report_type == 'transactions') {
            $status = 'success';

            $report = DB::SELECT("SELECT a.amount,b.account_name,b.account_number,a.created_at FROM tbl_transactions a inner join tbl_accounts b on a.account_id = b.id where a.created_at >='$report_from' and a.created_at <= '$report_to'");
        }elseif($report_type == 'loans'){
            $status = 'success';

            $report = DB::SELECT("SELECT a.amount,b.account_name,b.account_number,a.created_at FROM tbl_loans a inner join tbl_accounts b on a.account_id = b.id where a.created_at >= '$report_from' and a.created_at <= '$report_to'");
        }
        return json_encode(['status'=>$status,'report'=>$report]);
    }
}
