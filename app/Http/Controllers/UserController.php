<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminStoreRequest;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\UserStoreRequest;
use App\Models\Role;
use App\Models\Taxonomy;
use App\Models\Workspace;
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
        $q = User::whereIs('config', 'admin', 'content-manager', 'trainer', 'reports','only-reports');

        if ($request->has('q'))
           $q->filterText($request->q);

        $users = $q->orderBy('name')->paginate();

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
        $get_workspaces = Workspace::all()->where('parent_id', null);
        $get_roles = Role::all()->where('name', '!=', 'super-user');

        $workspaces = collect();
        $roles = collect();

        foreach ($get_workspaces as $wk) {
            $workspaces->push((object)[
                'id' => $wk->id,
                'name' => $wk->name,
                'slug' => $wk->slug,
            ]);
        }
        foreach ($get_roles as $rol) {
            $roles->push((object)[
                'id' => $rol->id,
                'name' => $rol->title,
                'slug' => $rol->name,
            ]);
        }

        return view('users.create', compact('roles', 'workspaces'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AdminStoreRequest $request)
    {
        //cambiar valor de name en el request
        $data = $request->all();
//        dd($data);

        $employee = Taxonomy::getFirstData('user', 'type', 'employee');
        $data['type_id'] = $employee->id;

        $user = User::create($data);

        if (isset($data['workspacessel']) && is_array($data['workspacessel']) && count($data['workspacessel']) > 0) {
            foreach ($data['workspacessel'] as $wk => $val) {
                if (isset($data['rolestowk'][$wk]) && is_array($data['rolestowk'][$wk]) && count($data['rolestowk'][$wk]) > 0 && !is_null($data['rolestowk'][$wk][0])) {
                    $roles = explode(',', $data['rolestowk'][$wk][0]);
                    Bouncer::scope()->to($data['workspacessel'][$wk][0]);
                    Bouncer::sync($user)->roles($roles);
                }
            }
        }

        return redirect()->route('users.index')
            ->with('info', 'Administrador guardado con éxito');
    }

    public function edit(User $user)
    {
        $get_workspaces = Workspace::all()->where('parent_id', null);
        $get_roles = Role::all()->where('name', '!=', 'super-user');

        $workspaces = collect();
        $roles = collect();

        foreach ($get_workspaces as $wk) {
            $workspaces->push((object)[
                'id' => $wk->id,
                'name' => $wk->name,
                'slug' => $wk->slug,
            ]);
        }
        foreach ($get_roles as $rol) {
            $roles->push((object)[
                'id' => $rol->id,
                'name' => $rol->title,
                'slug' => $rol->name,
            ]);
        }
        $workspaces_roles = [];

        foreach ($workspaces as $wkk => $wkv) {
            foreach ($user->roles as $rol) {

                $ids = [];
                if ($rol->name != 'super-user' && !is_null($rol->pivot->scope) && $rol->pivot->scope == $wkv->id) {
                    if (isset($workspaces_roles[$wkv->slug])) {
                        $workspaces_roles[$wkv->slug][] = [
                            'id' => $rol->pivot->role_id,
                            'name' => $rol->title,
                            'slug' => $rol->name,
                        ];
                    } else {
                        $workspaces_roles[$wkv->slug][] = [
                            'id' => $rol->pivot->role_id,
                            'name' => $rol->title,
                            'slug' => $rol->name,
                        ];
                    }
                }
            }
        }

        return view('users.edit', compact('user', 'workspaces', 'roles', 'workspaces_roles'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\user  $user
     * @return \Illuminate\Http\Response
     */
    public function update(AdminStoreRequest $request, User $user)
    {
        // 1. Actualizar el usuario
        $data = $request->all();
//        dd($data);

        if (!is_null($request->password)) {
            $data['password'] = $request->password;
        } else {
            unset($data['password']);
        }
        $user->update($data);

        // 2. Actualizar roles
        Bouncer::sync($user)->roles([]);
        if (isset($data['workspacessel']) && is_array($data['workspacessel']) && count($data['workspacessel']) > 0) {
            foreach ($data['workspacessel'] as $wk => $val) {
                if (isset($data['rolestowk'][$wk]) && is_array($data['rolestowk'][$wk]) && count($data['rolestowk'][$wk]) > 0 && !is_null($data['rolestowk'][$wk][0])) {
                    $roles = explode(',', $data['rolestowk'][$wk][0]);
                    Bouncer::scope()->to($data['workspacessel'][$wk][0]);
                    Bouncer::sync($user)->roles($roles);
                }
            }
        }

        return redirect()->route('users.index')
            ->with('info', 'Administrador actualizado con éxito');
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

        return back()->with('info', 'Administrador eliminado Correctamente');
    }
}
