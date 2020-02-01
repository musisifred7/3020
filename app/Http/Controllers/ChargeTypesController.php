<?php

namespace App\Http\Controllers;

use App\AccountType;
use App\Action;
use App\ChargeType;
use Auth;
use Illuminate\Http\Request;

class ChargeTypesController extends Controller
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
        $chargetypes = ChargeType::all();
        return view('charge_types.index')->with('chargetypes', $chargetypes);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $actions = Action::all();
        $accounttypes = AccountType::where('status', 'Active')->get();
        return view('charge_types.create', compact('actions', 'accounttypes'));
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
            'amount' => ['integer', 'required'],
            'action' => ['required', 'integer'],
            'accounttype' => 'required',
        ]);

        $chargetype = new ChargeType;

        $chargetype->name = $request->input('name');
        $chargetype->amount = $request->input('amount');
        $chargetype->account_type_id = $request->input('accounttype');
        $chargetype->action_id = $request->input('action');
        $chargetype->user_id = Auth::user()->id;
        $chargetype->save();

        return redirect('charge_types')->with('success', 'Charge type has been saved and activated!');
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
}