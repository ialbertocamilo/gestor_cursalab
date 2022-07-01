<?php

namespace App\Http\Controllers;

use App\Models\User;
use Caffeinated\Shinobi\Models\Role;
use Illuminate\Http\Request;
use App\Http\Requests\UserStoreRequest;
use Illuminate\Support\Facades\Hash;

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
            // return $question;
            $users = User::where('name', 'like', '%'.$question.'%')->paginate();
        }else{
            $users = User::paginate();
        }
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roles = Role::get();
        return view('users.create',compact('roles'));
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

        $user = User::create($data);

        $user->roles()->sync($request->get('roles'));

        return redirect()->route('users.index')
                ->with('info', 'usero guardado con éxito');
    }

    public function edit(user $user)
    {
        $roles = Role::get();
        return view('users.edit', compact('user','roles'));
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
        
        if (!is_null($request->password)) {
            $data['password'] = Hash::make($request->password);
        }else{
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
