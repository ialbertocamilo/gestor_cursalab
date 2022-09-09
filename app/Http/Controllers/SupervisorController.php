<?php

namespace App\Http\Controllers;

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
            ->get()
            ->map(function ($usuario) {
                if ($usuario->isSupervisor())
                    $usuario->fullname = $usuario->fullname . ' (es supervisor)';
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

        UserRelationship::createRelationAs(code: 'supervise', users: $data, model_type: null, model_id: null);

        return $this->success([]);
    }

    public function setDataSupervisor(Request $request)
    {
        Supervisor::setDataSupervisor($request->all());
        return $this->success([]);
    }

    public function destroy($supervisor)
    {
        Supervisor::deleteSupervisor($supervisor);
        return $this->success(['msg' => 'Se eliminó el supervisor correctamente.']);
    }

    public function getData($supervisor, $type)
    {
        $data = Supervisor::getData($supervisor, $type);
        return $this->success($data);
    }

    public function tipoCriterios()
    {
        $data = Supervisor::tipoCriterios();
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
