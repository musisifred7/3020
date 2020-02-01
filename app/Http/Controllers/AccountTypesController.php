<?php

namespace App\Http\Controllers;

use App\AccountType;
use Auth;
use Illuminate\Http\Request;

class AccountTypesController extends Controller
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
        $accounttypes = AccountType::all();
        return view('account_products.index')->with('accounttypes', $accounttypes);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('account_products.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'prefix' => ['unique:account_types'],
            'currency' => 'required',
        ]);

        $accounttype = new AccountType;

        $accounttype->name = $request->input('name');
        $accounttype->prefix = $request->input('prefix');
        $accounttype->currency = $request->input('currency');
        $accounttype->account_no_size = !is_null($request->input('account_no_size')) ? $request->input('account_no_size') : 6;
        $accounttype->default_account = !is_null($request->input('default_account')) ? $request->input('default_account') : 1;
        $accounttype->account_size = !is_null($request->input('account_size')) ? $request->input('account_size') : 1;
        $accounttype->min_balance = !is_null($request->input('min_balance')) ? $request->input('min_balance') : 0;
        $accounttype->max_balance = !is_null($request->input('max_balance')) ? $request->input('max_balance') : 0;
        $accounttype->details = $request->input('details');
        $accounttype->current_account = !is_null($request->input('default_account')) ? $request->input('default_account') : 1;
        $accounttype->type = $request->input('type');
        $accounttype->user_id = Auth::user()->id;
        $accounttype->save();

        return redirect('account_products/')->with('success', 'Account product has been created and activated!');
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
        $accounttype = AccountType::find($id);

        return view('account_products.edit')->with('accounttype',$accounttype);
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
}
