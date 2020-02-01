<?php

namespace App\Http\Controllers\Auth;

use App\CashAtHand;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
     */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function username()
    {
        $loginType = request()->input('username');
        $this->username = filter_var($loginType, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        request()->merge([$this->username => $loginType]);

        return property_exists($this, 'username') ? $this->username : 'email';
    }

    public function authenticated()
    {
        $cashAtHand = CashAtHand::orderBy('id', 'desc')->first();

        if (is_null($cashAtHand)) {
            $cashAtHand = new CashAtHand;

            $cashAtHand->processing_date = date('Y-m-d');
            $cashAtHand->type = 'B/F';
            $cashAtHand->amount = 0;
            $cashAtHand->balance = 0;
            $cashAtHand->total_capital = 0;
            $cashAtHand->user_id = 1;
            $cashAtHand->save();
        } else {
            if ($cashAtHand->processing_date < date('Y-m-d')) {

                $cash = new CashAtHand;

                $cash->processing_date = date('Y-m-d');
                $cash->type = 'B/F';
                $cash->amount = $cashAtHand->balance;
                $cash->balance = $cashAtHand->balance;
                $cash->total_capital = $cashAtHand->total_capital;
                $cash->user_id = 1;
                $cash->save();

                $cash = new CashAtHand;
                $cash->processing_date = date('Y-m-d');
                $cash->type = 'Transactions';
                $cash->amount = 0;
                $cash->balance = $cashAtHand->balance;
                $cash->total_capital = $cashAtHand->total_capital;
                $cash->user_id = 1;
                $cash->save();
            }
        }
    }
}