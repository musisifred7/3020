<?php

namespace App\Http\Controllers;

use App\Account;
use App\AccountType;
use App\Charge;
use App\ChargeType;
use App\Member;
use Auth;
use Illuminate\Http\Request;

class MembersController extends Controller
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
        $members = Member::all();
        return view('members.index')->with('members', $members);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $account_types = AccountType::where('status', 'Active')->get();
        return view('members.create')->with('account_types', $account_types);
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
            'firstname' => 'required',
            'lastname' => 'required',
            'gender' => 'required',
            'dob' => 'required',
            'id_type' => 'required',
            'id_number' => ['required', 'unique:members'],
            'work_type' => 'required',
            'work_address' => 'required',
            'phone_number' => ['required', 'unique:members'],
            'alt_phone_number' => ['unique:members,phone_number'],
            'home_address' => 'required',
            'accounts' => 'required',
        ]);

        // DB::beginTransaction();

        $new_member = new Member;

        $new_member->firstname = $request->input('firstname');
        $new_member->lastname = $request->input('lastname');
        $new_member->othername = $request->input('othername');
        $new_member->gender = $request->input('gender');
        $new_member->dob = $request->input('dob');
        $new_member->id_type = $request->input('id_type');
        $new_member->id_number = $request->input('id_number');
        $new_member->home_address = $request->input('home_address');
        $new_member->alt_home_address = $request->input('alt_home_address');
        $new_member->work_type = $request->input('work_type');
        $new_member->work_address = $request->input('work_address');
        $new_member->alt_work_address = $request->input('alt_work_address');
        $new_member->phone_number = $request->input('phone_number');
        $new_member->alt_phone_number = $request->input('alt_phone_number');
        $new_member->email = $request->input('email');
        $new_member->alt_email = $request->input('alt_email');
        $new_member->details = $request->input('details');
        $new_member->user_id = Auth::user()->id;
        $new_member->save();

        if ($new_member) {
            $accounts = $request->input('accounts');

            foreach ($accounts as $account) {

                $accounttype = AccountType::find($account);

                $account = new Account;
                $account->account_type_id = $accounttype->id;
                $account->member_id = $new_member->id;
                $account->account_name = trim($request->input('firstname') . ' ' . $request->input('lastname') . ' ' . $request->input('othername'));
                $account->account_number = $accounttype->prefix . str_pad($accounttype->current_account, $accounttype->account_no_size, "0", STR_PAD_LEFT);
                $account->user_id = Auth::user()->id;

                $account->save();

                $accounttype->current_account = $accounttype->current_account + 1;
                $accounttype->save();

                $chargetypes = ChargeType::where('account_type_id', $accounttype->id)->where('action_id', 1)->get();

                foreach ($chargetypes as $chargetype) {
                    $charge = new Charge;

                    $charge->charge_type_id = $chargetype->id;
                    $charge->account_id = $account->id;
                    $charge->amount = $chargetype->amount;
                    $charge->status = 'Unpaid';
                    $charge->save();
                }
            }

            return redirect('members/create')->with('success', 'Member has been created');
        } else {
            return redirect('members/create')->with('error', 'Unable to create member');
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
         $member = Member::find($id);

        return view('members.details')->with('member',$member);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $member = Member::find($id);
        return view('members.edit')->with('member',$member);
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
            'firstname' => 'required',
            'lastname' => 'required',
            'gender' => 'required',
            'dob' => 'required',
            'home_address' => 'required',
        ]);

         $member = Member::find($id);

        $member->firstname = $request->input('firstname');
        $member->lastname = $request->input('lastname');
        $member->othername = $request->input('othername');
        $member->gender = $request->input('gender');
        $member->dob = $request->input('dob');
        $member->home_address = $request->input('home_address');
        $member->alt_home_address = $request->input('alt_home_address');
        $member->alt_work_address = $request->input('alt_work_address');
        $member->phone_number = $request->input('phone_number');
        $member->alt_phone_number = $request->input('alt_phone_number');
        $member->email = $request->input('email');
        $member->alt_email = $request->input('alt_email');
        $member->save();

        return redirect('members/'.$member->id)->with('success','Member details has been updated');
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