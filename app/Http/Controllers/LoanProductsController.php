<?php

namespace App\Http\Controllers;

use App\LoanProduct;
use Auth;
use Illuminate\Http\Request;

class LoanProductsController extends Controller
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
        $loanproducts = LoanProduct::all();
        return view('loan_products.index')->with('loanproducts', $loanproducts);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('loan_products.create');
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
            'name' => ['required', 'unique:loan_products'],
            'type' => 'required',
            'interest' => ['required', 'integer'],
        ]);

        $loanproduct = new LoanProduct;
        $loanproduct->name = $request->input('name');
        $loanproduct->type = $request->input('type');
        $loanproduct->interest = $request->input('interest');
        $loanproduct->margin = !is_null($request->input('margin')) ? $request->input('margin') : 0;
        $loanproduct->details = $request->input('details');
        $loanproduct->user_id = Auth::user()->id;
        $loanproduct->save();

        return redirect('loan_products')->with('success', 'Loan product has been saved and activated!');
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
        $product = LoanProduct::find($id);

        return view('loan_products.edit')->with('product',$product);
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
            'name' => 'required',
            'type' => 'required',
            'interest' => ['required', 'integer'],
        ]);

        $loanproduct = LoanProduct::find($id);
        $loanproduct->name = $request->input('name');
        $loanproduct->type = $request->input('type');
        $loanproduct->interest = $request->input('interest');
        $loanproduct->margin = !is_null($request->input('margin')) ? $request->input('margin') : 0;
        $loanproduct->details = $request->input('details');
        $loanproduct->status = $request->input('status');
        $loanproduct->save();

        return redirect('loan_products')->with('success', 'Loan product has been successfully updated');
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
