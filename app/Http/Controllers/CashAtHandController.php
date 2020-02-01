<?php

namespace App\Http\Controllers;

use App\CashAtHand;
use Auth;
use Illuminate\Http\Request;

class CashAtHandController extends Controller
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
        $transactions = CashAtHand::orderBy('id', 'asc')->get();
        return view('cash_at_hand.index')->with('transactions', $transactions);
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

    public function deposit(Request $request)
    {
        $balance = CashAtHand::orderby('id', 'desc')->first();

        $old_balance = $balance->balance;
        $old_capital = $balance->total_capital;

        $amount = str_replace(',', '', $request->input("amount"));

        $new_balance = $old_balance + $amount;
        $new_capital = $old_capital + $amount;

        $cash_at_hand = new CashAtHand;

        $cash_at_hand->processing_date = date('Y-m-d');
        $cash_at_hand->type = 'Deposit';
        $cash_at_hand->amount = $amount;
        $cash_at_hand->balance = $new_balance;
        $cash_at_hand->details = $request->input("details");
        $cash_at_hand->total_capital = $new_capital;
        $cash_at_hand->user_id = Auth::user()->id;
        $cash_at_hand->save();

        $cash_at_hand = new CashAtHand;

        $cash_at_hand->processing_date = date('Y-m-d');
        $cash_at_hand->type = 'Transactions';
        $cash_at_hand->amount = 0;
        $cash_at_hand->balance = $new_balance;
        $cash_at_hand->total_capital = $new_capital;
        $cash_at_hand->user_id = 1;
        $cash_at_hand->save();

        if ($cash_at_hand) {
            $response = json_encode(['status' => 'success', 'message' => 'Deposit was successfull!']);
        } else {
            $response = json_encode(['status' => 'failed', 'message' => 'System was un able to make deposit!']);
        }

        return $response;
    }

    public function withdraw(Request $request)
    {
        $balance = CashAtHand::orderby('id', 'desc')->first();

        $old_balance = $balance->balance;
        $old_capital = $balance->total_capital;

        $amount = str_replace(',', '', $request->input("amount"));

        $new_balance = $old_balance - $amount;
        $new_capital = $old_capital - $amount;

        if ($new_balance > 0) {
            $cash_at_hand = new CashAtHand;

            $cash_at_hand->processing_date = date('Y-m-d');
            $cash_at_hand->type = 'Deposit';
            $cash_at_hand->amount = $amount;
            $cash_at_hand->balance = $new_balance;
            $cash_at_hand->details = $request->input("details");
            $cash_at_hand->total_capital = $new_capital;
            $cash_at_hand->user_id = Auth::user()->id;
            $cash_at_hand->save();

            $cash_at_hand = new CashAtHand;

            $cash_at_hand->processing_date = date('Y-m-d');
            $cash_at_hand->type = 'Transactions';
            $cash_at_hand->amount = 0;
            $cash_at_hand->balance = $new_balance;
            $cash_at_hand->total_capital = $new_capital;
            $cash_at_hand->user_id = 1;
            $cash_at_hand->save();

            if ($cash_at_hand) {
                $response = json_encode(['status' => 'success', 'message' => 'Deposit was successfull!']);
            } else {
                $response = json_encode(['status' => 'failed', 'message' => 'System was un able to make deposit!']);
            }
        } else {
            $response = json_encode(['status' => 'failed', 'message' => 'Insufficient funds in the system pool!']);
        }

        return $response;
    }
}