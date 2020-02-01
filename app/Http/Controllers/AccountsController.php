<?php

namespace App\Http\Controllers;

use App\Account;
use App\Loan;
use DB;
use Illuminate\Http\Request;

class AccountsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $accounts = Account::all();
        return view('accounts.index')->with('accounts', $accounts);
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
        $account = Account::find($id);
        $transactions = DB::SELECT("SELECT * FROM tbl_transactions where account_id='" . $account->id . "' order by id desc limit 5");
        return view('accounts.details', compact('account', 'transactions'));
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
        $request->validate([
            'account_name' => 'required',
            'status' => 'required',
        ]);

        $account = Account::find($id);

        $account->account_name = $request->input('account_name');
        $account->status = $request->input('status');
        $account->save();

        return redirect('accounts/'.$account->id)->with('success','Account details have been updated');
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

    public function loans($account_id)
    {
        $loans = Loan::where('account_id', $account_id)->get();
        $account = Account::find($account_id);

        return view('accounts.loans', compact('loans', 'account'));
    }

    public function suspended(){
        $accounts = Account::where('status','Suspended')->get();
        return view('accounts.suspended')->with('accounts', $accounts);
    }
}
