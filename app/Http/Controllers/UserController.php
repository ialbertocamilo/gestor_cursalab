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
        extract(User::getDataForAdminForm());

        $user['selected_workspaces'] = $data;

        return $this->success(compact('user', 'workspaces', 'roles', 'emails'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AdminStoreRequest $request)
    {
        $data = $request->validated();

        $data['type_id'] = Taxonomy::getFirstData('user', 'type', 'employee')->id;

        $user = User::create($data);

        $user->saveAdminData($data);

        return $this->success(['msg' => 'Administrador creado correctamente.']);
    }

    public function edit(User $user)
    {
        extract(User::getDataForAdminForm($user));

        $user->selected_workspaces = $data;

        return $this->success(compact('user', 'workspaces', 'roles', 'emails'));
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
        $data = $request->validated();

        $user->update($data);
        $user->saveAdminData($data);

        return $this->success(['msg' => 'Administrador actualizado correctamente.']);
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

        return $this->success(['msg' => 'Administrador eliminado correctamente.']);
    }

    public function status(User $user)
    {
        // info(!$user->active);
        $status = ($user->active == 1) ? 0 : 1;

        $user->update(['active' => $status]);
        
        return $this->success(['msg' => 'Administrador actualizado correctamente.']);
    }

    public function currentCourses($document){
        $user = User::where('document',$document)->first();
        $courses_id = [];
        if($user){
            $courses_id = $user->getCurrentCourses(only_ids:true);
        }
        return $this->success(['courses_id' => $courses_id]);
    }

    public function listUsersByCriteria(Request $request){
        $data = $request->all();
        $users = User::listUsersByCriteria($data);
        return $this->success(['users' => $users]);
    }
}
