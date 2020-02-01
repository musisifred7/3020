<?php

namespace App\Http\Controllers;

use App\Account;
use App\Charge;
use App\Loan;
use App\LoanProduct;
use App\LoanRepaymentSchedule;
use App\Transaction;
use Auth;
use DB;
use App\Http\Controllers\MethodsContrller;
use Illuminate\Http\Request;

class LoansController extends Controller
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
    	$loans = Loan::where('appl_status', 'Disbursed')->get();

    	return view('loans.index')->with('loans', $loans);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return self::test();
    }

    public function test(){
    	return 23;
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
    		'account_id' => ['integer', 'required'],
    		'amount' => 'required',
    		'interest' => 'required',
    		'payment_type' => 'required',
    		'loan_product' => 'required',
    		'duration' => 'required',
    	]);

    	DB::BeginTransaction();

    	try {
    		$amount = \str_replace(',', '', $request->input('amount'));
    		$interest = $request->input('interest');
    		$account_id = $request->input('account_id');
    		$payment_type = $request->input('payment_type');
    		$duration = $request->input('duration');
    		$loan_product = $request->input('loan_product');

    		$totalinterest = $amount * ($interest / 100);
    		$totalpay = $amount + $totalinterest;
    		$installment = $totalpay / $duration;
    		$interest_per_installment = $totalinterest / $duration;
    		$installments = $duration;

    		if ($payment_type == "Weekly") {
    			$installment = $installment / 4;
    			$installments = $duration * 4;

    			$interest_per_installment = $interest_per_installment / 4;
    		}

    		$loan = new Loan;

    		$loan->account_id = $account_id;
    		$loan->loan_product_id = $loan_product;
    		$loan->payment_type = $payment_type;
    		$loan->amount = $amount;
    		$loan->interest = $interest;
    		$loan->duration = $duration;
    		$loan->installament = $installment;
    		$loan->total_pay = $totalpay;
    		$loan->total_interest = $totalinterest;
    		$loan->appl_date = date('Y-m-d');
    		$loan->appl_status = 'Application';
    		$loan->appl_by = Auth::user()->id;
    		$loan->save();

    		if ($loan) {
    			$account = Account::find($account_id);
    			$charges = DB::SELECT("SELECT * FROM tbl_charge_types where action_id='4' AND account_type_id=" . $account->account_type_id . " ORDER BY amount ASC");

    			if (count($charges) > 0) {

    				$last_transaction = DB::SELECT("SELECT * FROM tbl_transactions where account_id = '$account_id' order By id desc limit 1");

    				if (count($last_transaction) > 0) {
    					$account_balance = $last_transaction[0]->balance;
    				} else {
    					$account_balance = 0;
    				}

    				foreach ($charges as $chargetype) {
    					$new_balance = $account_balance - $chargetype->amount;

    					if ($new_balance < 0) {
    						$charge = new Charge;

    						$charge->charge_type_id = $chargetype->id;
    						$charge->account_id = $account_id;
    						$charge->amount = $chargetype->amount;
    						$charge->status = 'Unpaid';
    						$charge->save();
    					} else {
    						$charge = new Charge;

    						$charge->charge_type_id = $chargetype->id;
    						$charge->account_id = $account_id;
    						$charge->amount = $chargetype->amount;
    						$charge->status = 'Paid';
    						$charge->save();

    						$transaction = new Transaction;

    						$transaction->account_id = $account_id;
    						$transaction->type = 'Charge';
    						$transaction->channel = 'System';
    						$transaction->amount = $chargetype->amount;
    						$transaction->balance = $new_balance;
    						$transaction->transaction_by = 'System';
    						$transaction->external_id = $charge->id;
    						$transaction->user_id = 1;
    						$transaction->save();
    					}

    				}

    				DB::Commit();
    			} else {
    				DB::Commit();
    			}
    			return redirect('/account_loans/' . $account_id)->with('success', 'Loan application has been successfully saved!');
    		} else {
    			DB::Rollback();
    			return redirect('/account_loans/' . $account_id)->with('warning', 'Unable to save loan application');
    		}
    	} catch (Exception $ex) {
    		DB::Rollback();
    		return redirect('/account_loans/' . $account_id)->with('warning', 'Unable to save loan application');
    	}
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
    	$loan = Loan::find($id);

    	return view('loans.details')->with('loan', $loan);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
    	$loan = Loan::find($id);
    	$account = Account::find($loan->account_id);
    	$loan_products = LoanProduct::where('status', 'Active')->get();
    	return view('loans.edit', compact('account', 'loan_products', 'loan'));
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
    	DB::BeginTransaction();
    	try {
    		$request->validate([
    			'account_id' => ['integer', 'required'],
    			'amount' => 'required',
    			'interest' => 'required',
    			'payment_type' => 'required',
    			'loan_product' => 'required',
    			'duration' => 'required',
    		]);

    		$amount = \str_replace(',', '', $request->input('amount'));
    		$interest = $request->input('interest');
    		$account_id = $request->input('account_id');
    		$payment_type = $request->input('payment_type');
    		$duration = $request->input('duration');
    		$loan_product = $request->input('loan_product');

    		$totalinterest = $amount * ($interest / 100);
    		$totalpay = $amount + $totalinterest;
    		$installment = $totalpay / $duration;
    		$interest_per_installment = $totalinterest / $duration;
    		$installments = $duration;

    		if ($payment_type == "Weekly") {
    			$installment = $installment / 4;
    			$installments = $duration * 4;

    			$interest_per_installment = $interest_per_installment / 4;
    		}

    		$loan = Loan::find($id);

    		$loan->loan_product_id = $loan_product;
    		$loan->payment_type = $payment_type;
    		$loan->amount = $amount;
    		$loan->interest = $interest;
    		$loan->duration = $duration;
    		$loan->installament = $installment;
    		$loan->total_pay = $totalpay;
    		$loan->total_interest = $totalinterest;
    		$loan->appl_status = 'Application';
    		$loan->save();

    		if ($loan) {
    			$account = Account::find($account_id);
    			$charges = DB::SELECT("SELECT * FROM tbl_charge_types where action_id='5' AND account_type_id=" . $account->account_type_id . " ORDER BY amount ASC");

    			if (count($charges) > 0) {

    				$last_transaction = DB::SELECT("SELECT * FROM tbl_transactions where account_id = '$account_id' order By id desc limit 1");

    				if (count($last_transaction) > 0) {
    					$account_balance = $last_transaction[0]->balance;
    				} else {
    					$account_balance = 0;
    				}

    				foreach ($charges as $chargetype) {
    					$new_balance = $account_balance - $chargetype->amount;

    					if ($new_balance < 0) {
    						$charge = new Charge;

    						$charge->charge_type_id = $chargetype->id;
    						$charge->account_id = $account_id;
    						$charge->amount = $chargetype->amount;
    						$charge->status = 'Unpaid';
    						$charge->save();
    					} else {
    						$charge = new Charge;

    						$charge->charge_type_id = $chargetype->id;
    						$charge->account_id = $account_id;
    						$charge->amount = $chargetype->amount;
    						$charge->status = 'Paid';
    						$charge->save();

    						$transaction = new Transaction;

    						$transaction->account_id = $account_id;
    						$transaction->type = 'Charge';
    						$transaction->channel = 'System';
    						$transaction->amount = $chargetype->amount;
    						$transaction->balance = $new_balance;
    						$transaction->transaction_by = 'System';
    						$transaction->external_id = $charge->id;
    						$transaction->user_id = 1;
    						$transaction->save();
    					}

    				}

    				DB::Commit();
    			} else {
    				DB::Commit();
    			}
    			return redirect('/account_loans/' . $account_id)->with('success', 'Loan application has been successfully updated!');
    		} else {
    			DB::Rollback();
    			return redirect('/account_loans/' . $account_id)->with('warning', 'Unable to edit loan application');
    		}
    		return redirect('/account_loans/' . $account_id)->with('success', 'Loan application has been successfully updated!');
    	} catch (Exception $th) {
    		DB::Rollback();
    		return redirect('/account_loans/' . $account_id)->with('warning', 'Unable to edit loan application');
    	}
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

    public function new_loan($account_id)
    {
    	$account = Account::find($account_id);
    	$loan_products = LoanProduct::where('status', 'Active')->get();
    	return view('loans.new_loan', compact('account', 'loan_products'));
    }

    public function get_product(Request $request)
    {
    	$product = LoanProduct::find($request->input('product_id'));

    	if (!is_null($product)) {
    		$status = "success";
    		$message = $product;
    	} else {
    		$status = "failed";
    		$message = "Unable to get loan product details";
    	}

    	return json_encode(['status' => $status, 'message' => $message]);
    }

    public function approve_loan($loan_id)
    {
    	DB::BeginTransaction();
    	try {
    		$loan = Loan::find($loan_id);

    		$loan->appl_status = 'Approved';
    		$loan->approved_by = Auth::user()->id;
    		$loan->approved_date = date('Y-m-d');
    		$loan->save();

    		$account_id = $loan->account_id;

    		if ($loan) {
    			$account = Account::find($loan->account_id);
    			$charges = DB::SELECT("SELECT * FROM tbl_charge_types where action_id='6' AND account_type_id=" . $account->account_type_id . " ORDER BY amount ASC");

    			if (count($charges) > 0) {

    				$last_transaction = DB::SELECT("SELECT * FROM tbl_transactions where account_id = '$account_id' order By id desc limit 1");

    				if (count($last_transaction) > 0) {
    					$account_balance = $last_transaction[0]->balance;
    				} else {
    					$account_balance = 0;
    				}

    				foreach ($charges as $chargetype) {
    					$new_balance = $account_balance - $chargetype->amount;

    					if ($new_balance < 0) {
    						$charge = new Charge;

    						$charge->charge_type_id = $chargetype->id;
    						$charge->account_id = $account_id;
    						$charge->amount = $chargetype->amount;
    						$charge->status = 'Unpaid';
    						$charge->save();
    					} else {
    						$charge = new Charge;

    						$charge->charge_type_id = $chargetype->id;
    						$charge->account_id = $account_id;
    						$charge->amount = $chargetype->amount;
    						$charge->status = 'Paid';
    						$charge->save();

    						$transaction = new Transaction;

    						$transaction->account_id = $account_id;
    						$transaction->type = 'Charge';
    						$transaction->channel = 'System';
    						$transaction->amount = $chargetype->amount;
    						$transaction->balance = $new_balance;
    						$transaction->transaction_by = 'System';
    						$transaction->external_id = $charge->id;
    						$transaction->user_id = 1;
    						$transaction->save();
    					}

    				}

    				DB::Commit();
    			} else {
    				DB::Commit();
    			}
    			return redirect('/account_loans/' . $account_id)->with('success', 'Loan application has been successfully approved!');
    		} else {
    			DB::Rollback();
    			return redirect('/account_loans/' . $account_id)->with('warning', 'Unable to approve loan application');
    		}

    	} catch (\Throwable $th) {
    		DB::Rollback();
    		return redirect('/account_loans/' . $account_id)->with('warning', 'Unable to approve loan application');
    	}

    	return redirect('/account_loans/' . $loan->account_id)->with('success', 'Loan application has been successfully approved!');
    }

    public function disburse_loan($loan_id)
    {
    	DB::BeginTransaction();
    	$loan = Loan::find($loan_id);
    	$account_id = $loan->account_id;

    	$cash_at_hand = DB::SELECT("SELECT balance from tbl_cash_at_hands order by id desc limit 1");

    	if ($cash_at_hand[0]->balance < $loan->amount) {
    		return redirect('/account_loans/' . $loan->account_id)->with('warning', 'Unable to disburse loan due to insufficient cash at hand!');
    	}

    	try {

    		$principle = $loan->amount;
    		$duration = $loan->duration;
    		$payment_type = $loan->payment_type;

    		$totalinterest = $loan->total_interest;
    		$totalpay = $principle + $totalinterest;
    		$installment = $totalpay / $duration;
    		$interest_per_installment = $totalinterest / $duration;
    		$installments = $duration;

    		if ($payment_type == "Weekly") {
    			$installment = $installment / 4;
    			$installments = $duration * 4;

    			$interest_per_installment = $interest_per_installment / 4;
    		}

    		$paymentdate = date('Y-m-d');

    		if ($payment_type == "Weekly") {

    			for ($i = 0; $i <= $installments; $i++) {
    				$paymentdate = date('Y-m-d', strtotime("+1 weeks", strtotime($paymentdate)));
    				$repayment_schedule = new LoanRepaymentSchedule;

    				$repayment_schedule->loan_id = $loan->id;
    				$repayment_schedule->amount = $installment;
    				$repayment_schedule->interest = $interest_per_installment;
    				$repayment_schedule->balance = $installment;
    				$repayment_schedule->due_date = $paymentdate;
    				$repayment_schedule->status = 'Due';
    				$repayment_schedule->save();
    			}
    		}

    		if ($payment_type == "Monthly") {

    			for ($i = 0; $i < $installments; $i++) {
    				$paymentdate = date('Y-m-d', strtotime("+1 months", strtotime($paymentdate)));

    				$repayment_schedule = new LoanRepaymentSchedule;

    				$repayment_schedule->loan_id = $loan->id;
    				$repayment_schedule->amount = $installment;
    				$repayment_schedule->interest = $interest_per_installment;
    				$repayment_schedule->balance = $installment;
    				$repayment_schedule->due_date = $paymentdate;
    				$repayment_schedule->status = 'Due';
    				$repayment_schedule->save();
    			}
    		}

    		$loan->appl_status = 'Disbursed';
    		$loan->loan_status = 'Running';
    		$loan->disbursed_by = Auth::user()->id;
    		$loan->disbursed_date = date('Y-m-d');
    		$loan->save();


    		$transaction = Transaction::where('account_id',$account_id)->get();

    		if (count($transaction) <= 0) {
    			$account_balance = 0;
    		}else{
    			$account_balance = $transaction[0]->balance;
    		}

    		$loan_deposit = new Transaction;
    		$loan_deposit->account_id = $account_id;
    		$loan_deposit->type = 'Deposit';
    		$loan_deposit->channel = 'System';
    		$loan_deposit->amount = $principle;
    		$loan_deposit->balance = $account_balance + $principle;
    		$loan_deposit->details = 'Loan deposit';
    		$loan_deposit->transaction_by = 1;
    		$loan_deposit->external_id = $loan->id;
    		$loan_deposit->user_id = 1;
    		$loan_deposit->save();


    		if ($loan) {
    			$account = Account::find($loan->account_id);
    			$charges = DB::SELECT("SELECT * FROM tbl_charge_types where action_id='7' AND account_type_id=" . $account->account_type_id . " ORDER BY amount ASC");

    			if (count($charges) > 0) {

    				$last_transaction = DB::SELECT("SELECT * FROM tbl_transactions where account_id = '$account_id' order By id desc limit 1");

    				if (count($last_transaction) > 0) {
    					$account_balance = $last_transaction[0]->balance;
    				} else {
    					$account_balance = 0;
    				}

    				foreach ($charges as $chargetype) {
    					$new_balance = $account_balance - $chargetype->amount;

    					if ($new_balance < 0) {
    						$charge = new Charge;

    						$charge->charge_type_id = $chargetype->id;
    						$charge->account_id = $account_id;
    						$charge->amount = $chargetype->amount;
    						$charge->status = 'Unpaid';
    						$charge->save();
    					} else {
    						$charge = new Charge;

    						$charge->charge_type_id = $chargetype->id;
    						$charge->account_id = $account_id;
    						$charge->amount = $chargetype->amount;
    						$charge->status = 'Paid';
    						$charge->save();

    						$transaction = new Transaction;

    						$transaction->account_id = $account_id;
    						$transaction->type = 'Charge';
    						$transaction->channel = 'System';
    						$transaction->amount = $chargetype->amount;
    						$transaction->balance = $new_balance;
    						$transaction->transaction_by = 'System';
    						$transaction->external_id = $charge->id;
    						$transaction->user_id = 1;
    						$transaction->save();
    					}

    				}

    				DB::Commit();
    			} else {
    				DB::Commit();
    			}
    			return redirect('/account_loans/' . $account_id)->with('success', 'Loan application has been successfully disbursed!');
    		} else {
    			DB::Rollback();
    			return redirect('/account_loans/' . $account_id)->with('warning', 'Unable to disburse loan application');
    		}
    	} catch (\Throwable $th) {
    		DB::Rollback();
    		return redirect('/account_loans/' . $account_id)->with('warning', 'Unable to disburse loan application'.$th);
    	}

    	return redirect('/account_loans/' . $loan->account_id)->with('success', 'Loan has been successfully disbused!');
    }

    public function cancel_loan($loan_id)
    {
    	$loan = Loan::find($loan_id);

    	$loan->appl_status = 'Cancelled';
    	$loan->cancelled_by = Auth::user()->id;
    	$loan->cancelled_date = date('Y-m-d');
    	$loan->save();

    	return redirect('/account_loans/' . $loan->account_id)->with('success', 'Loan application has been successfully cancelled!');
    }

    public function loan_applications()
    {
    	$loans = Loan::where('appl_status', 'Application')->get();

    	return view('loans.applications')->with('loans', $loans);
    }

    public function approved_loans()
    {

    	$loans = Loan::where('appl_status', 'Approved')->get();

    	return view('loans.approved')->with('loans', $loans);
    }

    public function running_loans()
    {
    	$loans = Loan::where('loan_status', 'Running')->get();

    	return view('loans.running')->with('loans', $loans);
    }

    public function complete_loans(){
        $loans = Loan::where('loan_status', 'Complete')->get();

        return view('loans.complete')->with('loans', $loans);
    }

    public function cancelled_loans()
    {
        $loans = Loan::where('loan_status', 'Cancelled')->get();

        return view('loans.cancelled')->with('loans', $loans);
    }

     public function denied_loans()
    {
        $loans = Loan::where('appl_status', 'Cancelled')->get();

        return view('loans.denied')->with('loans', $loans);
    }
}
