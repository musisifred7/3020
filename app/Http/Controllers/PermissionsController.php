<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\User;
use Auth;

class PermissionsController extends Controller
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
        //Roles [Settings,]

        // $role = Role::create(['name' => 'Settings']);

        // $permission = Permission::create(['name' => 'Assign permission']);

        // $role->givePermissionTo($permission);

        // $role = Role::findById(1);
        // $permission = Permission::findById(7);

        // $role->givePermissionTo($permission);
        $permissions = DB::SELECT('SELECT * FROM tbl_permissions');
        return view('permissions.index')->with('permissions', $permissions);
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
        $role = Role::create(['name' => 'Settings']);
        $permission = Permission::create(['name' => $request->input('name')]);

        return redirect('permissions')->with('success', 'Permission has been created');
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

    public function manage(Request $request){
        $user = $request->input('user');
        $user = User::find($user);

        if($request->input('action') == 1){
            $user->givePermissionTo($request->input('permission'));
            $message = "Permission has been granted";
        }else{
            $user->revokePermissionTo($request->input('permission'));
            $message = "Permission has been revoked";
        }
        
        return ['status'=>true,'message'=>$message];
    }
}
