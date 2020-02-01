<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\AssociatedMember;

class AssociatedMembersController extends Controller
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
        //
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
        $request->validate([
            'account_id' => 'required',
            'members' => 'required',
        ]);

        $account_id = $request->input('account_id');

        $members = $request->input('members');
        foreach ($members as $member) {
            $associatedMember = new AssociatedMember;

            $associatedMember->account_id = $account_id;
            $associatedMember->name = $member['name'];
            $associatedMember->address = $member['address'];
            $associatedMember->contact = $member['contact'];
            $associatedMember->save();
        }

        return redirect('accounts/'.$account_id)->with('success','Associated member(s) have been added to this account!');
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
