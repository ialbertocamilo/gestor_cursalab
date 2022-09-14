<?php

namespace App\Http\Controllers;

use App\Models\Criterion;
use App\Models\Supervisor;
use App\Models\User;
use App\Models\UserRelationship;
use App\Models\Workspace;
use Illuminate\Http\Request;
use App\Http\Resources\SupervisorResource;

class SupervisorController extends Controller
{
    public function search(Request $request)
    {
        $workspace = get_current_workspace();
        $request->mergeIfMissing(['workspace' => $workspace?->id]);
        $request->mergeIfMissing(['code' => 'supervise']);

        $data = UserRelationship::search($request);

        SupervisorResource::collection($data);
        return $this->success($data);
    }

    public function modulos()
    {
        $workspace = get_current_workspace();

        $subworkspaces = Workspace::select('id', 'name')
            ->whereRelation('parent', 'id', $workspace->id)
            ->get();

        return $this->success(['modulos' => $subworkspaces]);
    }

    public function searchUsuarios(Request $request)
    {
        $users = User::filterText($request->filtro)
            ->select('id', 'document', 'name', 'lastname', 'surname')
            ->onlyAppUser()
            ->limit(40)
            ->get();

        $users->map(function ($user) {
            if ($user->isSupervisor())
                $user->fullname = $user->fullname . ' (es supervisor)';
        });

        return $this->success(['users' => $users]);
    }

    public function subirExcelUsuarios(Request $request)
    {
        $data = Supervisor::subirExcelUsuarios($request);
        return response()->json($data, 200);
    }

    public function setUsuariosAsSupervisor(Request $request)
    {
        $data = $request->all();

        $users_id = array_column($data, 'id');

        $users = User::with('subworkspace.module_criterion_value')->whereIn('id', $users_id)->get();

        UserRelationship::setUsersAsSupervisor($users);

        return $this->success([]);
    }

    public function setDataSupervisor(Request $request)
    {
//        Supervisor::setDataSupervisor($request->all());
        UserRelationship::setDataSupervisor($request->all());
        return $this->success([]);
    }

    public function destroy($supervisor)
    {
        Supervisor::deleteSupervisor($supervisor);
        return $this->success(['msg' => 'Se eliminó el supervisor correctamente.']);
    }

    public function getData(User $supervisor, $type)
    {
        $data = UserRelationship::getDataToAssignSupervised($supervisor, $type);

        return $this->success($data);
    }

    public function tipoCriterios()
    {
        $data = Criterion::select('name as nombre', 'id')->get();
        return $this->success($data);
    }


    public function setCriterioGlobalesSupervisor(Request $request)
    {
        $data = Supervisor::setCriterioGlobalesSupervisor($request);
        return $this->success([]);
    }

    public function searchSupervisores(Request $request)
    {
        $data = Supervisor::searchSupervisores($request);
        return $this->success($data);
    }
}
