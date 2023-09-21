<?php

namespace App\Http\Controllers;

use Bouncer;
use App\Models\Role;
use App\Models\User;
use App\Models\Taxonomy;
use App\Models\EmailUser;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\UserStoreRequest;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\AdminStoreRequest;
use App\Http\Resources\UserSearchResource;
use Illuminate\Support\Facades\Redirect;
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
        // $q = User::whereIs('config', 'admin', 'content-manager', 'trainer', 'reports','only-reports');
        $q = User::whereRelation('roles', function ($query) {
            $query->where('name', '<>', 'super-user');
        });
        if ($request->has('q'))
           $q->filterText($request->q);

        $users = $q->orderBy('id','desc')->paginate();

        $super_user = auth()->user()->isAn('super-user');
        return view('users.index', compact('users', 'super_user'));
    }

    public function search(Request $request)
    {
        $workspace = get_current_workspace();
        // $sub_workspaces_id = $workspace?->subworkspaces?->pluck('id');

        // $request->merge(['sub_workspaces_id' => $sub_workspaces_id, 'superuser' => auth()->user()->isA('super-user')]);

        $users = User::searchAdmins($request);

        UserSearchResource::collection($users);

        return $this->success($users);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $workspaces = Workspace::with('subworkspaces:id,name')->where('parent_id', null)->get();
        $roles = Role::where('name', '!=', 'super-user')->get();
        
        $emails_information = Taxonomy::select('id','name')->where('group','email')->where('type','user')->get();
        $emails_information_selected = '';

        // return view('users.create', compact('roles', 'workspaces', 'emails_information', 'emails_information_selected'));
        return $this->success(compact('workspaces', 'roles', 'emails_information', 'emails_information_selected'));
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
        EmailUser::storeUpdate($data,$user);
        return redirect()->route('users.index')
            ->with('info', 'Administrador guardado con éxito');
    }

    public function edit(User $user)
    {
        $_workspaces = $user->getWorkspaces();

        $workspaces = Workspace::with('subworkspaces')->where('parent_id', null)->get();
        $roles = Role::where('name', '!=', 'super-user')->get();
        
        $emails_information = Taxonomy::select('id','name')->where('group','email')->where('type','user')->get();
        $emails_information_selected = $user->emails_user()->with('type:id,name')->get();

        // return $this->success(compact('_workspaces'));
        return $this->success(compact('user', 'workspaces', 'roles', 'emails_information','emails_information_selected'));
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

        dd($data);

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
        //Actualizar envío de emails
        EmailUser::storeUpdate($data,$user);
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
