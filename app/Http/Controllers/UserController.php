<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\UserStoreRequest;
use App\Models\Role;
use App\Models\Taxonomy;
use Illuminate\Support\Facades\Hash;
use Bouncer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Silber\Bouncer\Database\Role as DatabaseRole;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->has('q')) {
            $question = $request->input('q');
            $users = User::whereIsNot('user', 'super-user')->where('name', 'like', '%' . $question . '%')->paginate();
        } else {
            $users = User::whereIsNot('user', 'super-user')->paginate();
        }
        $super_user = auth()->user()->isAn('super-user');
        return view('users.index', compact('users', 'super_user'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roles = Role::get();
        return view('users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserStoreRequest $request)
    {
        //cambiar valor de name en el request
        $data = $request->all();
        $data['password'] = Hash::make($request->password);

        $workspace = session('workspace');
        $workspace_id = (is_array($workspace)) ? $workspace['id'] : null;

        $employee = Taxonomy::getFirstData('user', 'type', 'employee');
        $data['type_id'] = $employee->id;
        $data['workspace_id'] = $workspace_id;
        $user = User::create($data);

        $user->roles()->sync($request->get('roles'));

        return redirect()->route('users.index')
            ->with('info', 'usero guardado con éxito');
    }

    public function edit(user $user)
    {
        $roles = Role::get();
        return view('users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\user  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, user $user)
    {
        // 1. Actualizar el usuario
        $data = $request->all();

        // dd($request->password, $user->password);

        if (!is_null($request->password)) {
            $data['password'] = Hash::make($request->password);
        } else {
            $data['password'] = $user->password;
        }


        $user->update($data);

        // 2. Actualizar roles
        $user->roles()->sync($request->get('roles'));

        return redirect()->route('users.index')
            ->with('info', 'Actualizado con éxito');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\user  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(user $user)
    {
        $user->delete();

        return back()->with('info', 'Eliminado Correctamente');
    }
}
