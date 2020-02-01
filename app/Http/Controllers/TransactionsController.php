<?php

namespace App\Http\Controllers;

use App\Account;
use App\ChargeType;
use App\Charge;
use App\Transaction;
use App\CashAtHand;
use DB;
use Auth;
use Illuminate\Http\Request;

class TransactionsController extends Controller
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
        $account = Account::find(1);
        return $account->account_type->charge_types;
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

    }

    public function deposit(Request $request)
    {
        $status = "failed";
        $message = "Unable to make deposit";

        DB::beginTransaction();

        try {

            $amount = str_replace(',', '', $request->input('amount'));
            $depositor = $request->input('depositor');
            $details = $request->input('details');
            $account_id = $request->input('account_id');

            $account = Account::find($account_id);
            $last_transaction = Transaction::orderby('id', 'desc')->first();

            if (is_null($last_transaction)) {
                $old_balance = 0;
            } else {
                $old_balance = $last_transaction->balance;
            }

            $new_balance = $old_balance + $amount;

            $account_type = $account->account_type_id;
            $min_balance = $account->account_type->min_balance;
            $max_balance = $account->account_type->max_balance;

            $charge_types = ChargeType::where('account_type_id', $account_type)->where('action_id', 2)->get();

            $total_charge = 0;

            if (!is_null($charge_types)) {
                foreach ($charge_types as $charge_type) {
                    $total_charge = $charge_type->amount + $total_charge;
                }
            }
            


            $balance_after_charge = $new_balance - $total_charge;

            if ($balance_after_charge < $min_balance) {
                $message = "Your deposit can not be below: $total_charge";
            } else {
                $transaction = new Transaction;

                $transaction->account_id = $account->id;
                $transaction->type = 'Deposit';
                $transaction->channel = 'Cash';
                $transaction->amount = $amount;
                $transaction->balance = $new_balance;
                $transaction->details = $request->input('details');
                $transaction->transaction_by = $request->input('depositor');
                $transaction->user_id = Auth::user()->id;
                $transaction->save();

                $new_balance = $new_balance;

                if (!is_null($charge_types)) {
    # code...

                    foreach ($charge_types as $charge_type) {

                        $charge = new Charge;

                        $charge->charge_type_id = $charge_type->id;
                        $charge->account_id = $account->id;
                        $charge->amount = $charge_type->amount;
                        $charge->status = 'Paid';
                        $charge->save();


                        $transaction = new Transaction;

                        $transaction->account_id = $account->id;
                        $transaction->type = 'Charge';
                        $transaction->channel = 'System';
                        $transaction->amount = $charge_type->amount;
                        $transaction->balance = $new_balance - $charge_type->amount;
                        $transaction->details = $request->input('details');
                        $transaction->transaction_by = 'System';
                        $transaction->external_id = $charge->id;
                        $transaction->user_id = 1;
                        $transaction->save();

                        $new_balance = $new_balance - $charge_type->amount;
                    }
                }

                $cash_at_hand = CashAtHand::orderby('id','desc')->first();

                $total_balance = $cash_at_hand->balance;
                $total_amount = $cash_at_hand->amount;
                $total_capital = $cash_at_hand->total_capital;

                $cash_at_hand->amount = $total_amount + $amount;
                $cash_at_hand->balance = $total_balance + $amount;
                $cash_at_hand->total_capital = $total_capital + $amount;
                $cash_at_hand->save();

                if ($cash_at_hand) {
                    DB::commit();
                    $status = 'success';
                    $message = "Deposit has been successfull!";

                }else{
                    DB::rollback();
                    $message = 'Unable to make deposit';
                }
            }

        } catch (Exception $ex) {
            DB::rollback();
            $message = "Unable to make deposit";
        }

        $response = json_encode(['status' => $status, 'message' => $message]);

        return $response;
    }

    public function withdraw(Request $request)
    {
        $status = "failed";
        $message = "Unable to make withdraw";

        DB::beginTransaction();

        try {

            $amount = str_replace(',', '', $request->input('amount'));
            $details = $request->input('details');
            $account_id = $request->input('account_id');                
            
            $cash_at_hand = CashAtHand::orderby('id','desc')->first();

            $total_balance = $cash_at_hand->balance;
            $total_amount = $cash_at_hand->amount;
            $total_capital = $cash_at_hand->total_capital;

            if($total_balance < $amount){
                $message = "Insufficient cash at hand!";
            }else{
                $account = Account::find($account_id);
                $account_type = $account->account_type_id;
                $min_balance = $account->account_type->min_balance;
                $max_balance = $account->account_type->max_balance;
                
                $last_transaction = Transaction::orderby('id', 'desc')->first();

                if (is_null($last_transaction)) {
                    $message = "Insufficient funds on this account!";
                } else {
                    $old_balance = $last_transaction->balance;

                    $new_balance = $old_balance - $amount;

                    if($new_balance < 0){
                        $message = "Insufficient funds on this account!";
                    }elseif ($new_balance < $min_balance) {
                        $message = "Account can not be withdrawn beyond minimum balance: $min_balance";
                    }else{

                        $charge_types = ChargeType::where('account_type_id', $account_type)->where('action_id', 3)->get();

                        $total_charge = 0;

                        if(!is_null($charge_types)){
                            foreach ($charge_types as $charge_type) {
                                $total_charge = $charge_type->amount + $total_charge;
                            }
                        }

                        $balance_after_charge = $new_balance - $total_charge;

                        if ($balance_after_charge < 0  || $balance_after_charge < $min_balance) {
                            $message = "Insufficient funds on this account!";
                        }else{
                            $transaction = new Transaction;

                            $transaction->account_id = $account->id;
                            $transaction->type = 'Withdraw';
                            $transaction->channel = 'Cash';
                            $transaction->amount = $amount;
                            $transaction->balance = $new_balance;
                            $transaction->details = $request->input('details');
                            $transaction->user_id = Auth::user()->id;
                            $transaction->save();

                            $new_balance = $new_balance;

                            if(!is_null($charge_types)){
                                foreach ($charge_types as $charge_type) {

                                    $charge = new Charge;

                                    $charge->charge_type_id = $charge_type->id;
                                    $charge->account_id = $account->id;
                                    $charge->amount = $charge_type->amount;
                                    $charge->status = 'Paid';
                                    $charge->save();


                                    $transaction = new Transaction;

                                    $transaction->account_id = $account->id;
                                    $transaction->type = 'Charge';
                                    $transaction->channel = 'System';
                                    $transaction->amount = $charge_type->amount;
                                    $transaction->balance = $new_balance - $charge_type->amount;
                                    $transaction->details = $request->input('details');
                                    $transaction->transaction_by = 'System';
                                    $transaction->external_id = $charge->id;
                                    $transaction->user_id = 1;
                                    $transaction->save();

                                    $new_balance = $new_balance - $charge_type->amount;


                                }
                                $cash_at_hand->amount = $total_amount + $amount;
                                $cash_at_hand->balance = $total_balance - $amount;
                                $cash_at_hand->total_capital = $total_capital - $amount;
                                $cash_at_hand->save();

                                if ($cash_at_hand) {
                                    DB::commit();
                                    $status = 'success';
                                    $message = "Withdraw has been successfull!";

                                }else{
                                    DB::rollback();
                                    $message = 'Unable to make withdraw';
                                }
                            }
                        }
                    }

                }

            }

        } catch (Exception $ex) {
            DB::rollback();
            $message = "Unable to make withdraw";
        }

        $response = json_encode(['status' => $status, 'message' => $message]);

        return $response;
    }

    public function account_balance(Request $request){
        $account_balance = Transaction::where('account_id',$request->input('account_id'))->orderby('id','desc')->first();

        if(is_null($account_balance)){
            $status = "failed";
            $message = "Your account balance is: 0";
        }else{
            $status = "success";
            $message = "Your account balance is: ".number_format($account_balance->balance);
        }

        return json_encode(['status'=>"$status",'message'=>"$message"]);
    }
}
