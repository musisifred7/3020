<?php

namespace App\Http\Controllers;

use App\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
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
        $users = User::where('account_type', '=', 'Staff')->where('id','!=',Auth::user()->id)->get();
        return view('users.index')->with('users', $users);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('users.create');
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
            'email' => ['required', 'unique:users'],
            'username' => ['required', 'unique:users'],
            'password' => ['min:6', 'required', 'confirmed'],
            'account_type' => 'required',
        ]);

        $user = new User;

        $user->firstname = $request->input('firstname');
        $user->lastname = $request->input('lastname');
        $user->othername = $request->input('othername');
        $user->gender = $request->input('gender');
        $user->dob = $request->input('dob');
        $user->email = $request->input('email');
        $user->phone_no = $request->input('phone_no');
        $user->alt_phone_no = $request->input('alt_phone_no');
        $user->username = $request->input('username');
        $user->password = Hash::make($request->input('password'));
        $user->account_type = $request->input('account_type');
        $user->user_id = Auth::user()->id;
        $user->save();

        return redirect('/users')->with('success', 'Person has been saved');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::find($id);
        return view('users.show')->with('user',$user);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::find($id);
        return view('users.edit')->with('user', $user);
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
            'account_type' => 'required',
        ]);

        $user = User::find($id);

        $user->firstname = $request->input('firstname');
        $user->lastname = $request->input('lastname');
        $user->othername = $request->input('othername');
        $user->gender = $request->input('gender');
        $user->dob = $request->input('dob');
        $user->phone_no = $request->input('phone_no');
        $user->alt_phone_no = $request->input('alt_phone_no');
        $user->account_type = $request->input('account_type');
        $user->save();

        return redirect('/users/' . $user->id . '/edit')->with('success', 'Person\'s information has been updated successfully!');
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