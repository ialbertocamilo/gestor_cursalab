<?php

namespace App\Http\Controllers;

use App\Models\Criterion;
use App\Models\Segment;
use App\Models\Supervisor;
use App\Models\Taxonomy;
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

//        $data = UserRelationship::search($request);

        $data = User::query()
            ->whereHas('segments')
            ->withCount([
                'segments' => function ($q) {
                    $q->
                    whereRelation('code', 'code', 'user-supervise');
                },
            ])
            ->whereRelation('segments.code', 'code', 'user-supervise');

        $data->withWhereHas('subworkspace', function ($query) use ($request) {
            if ($request->subworkspace)
                $query->whereIn('id', $request->subworkspace);
            else
                $query->where('parent_id', $request->workspace);
        });

        if ($request->has('q'))
            $data->filterText($request->q);

        $field = $request->sortBy ?? 'created_at';
        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';

        $data = $data->orderBy($field, $sort)->paginate($request->paginate);

        SupervisorResource::collection($data);
        return $this->success($data);
    }

    public function modulos()
    {
        // $workspace = get_current_workspace();
        $sub_workspaces_id = current_subworkspaces_id();

        $subworkspaces = Workspace::select('id', 'name')
            // ->whereRelation('parent', 'id', $workspace->id)
            ->whereIn('id', $sub_workspaces_id)
            ->get();

        return $this->success(['modulos' => $subworkspaces]);
    }

    public function searchUsuarios(Request $request)
    {
        $workspace = get_current_workspace();

        $users = User::filterText($request->filtro)
            ->select('id', 'document', 'name', 'lastname', 'surname')
            ->whereRelation('subworkspace', 'parent_id', $workspace->id)
            ->onlyClientUsers()
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

//        $users_id = array_column($data, 'id');
        $user_documents = array_column($data, 'document');

        $users = User::with('subworkspace.module_criterion_value')->whereIn('document', $user_documents)->get();

        $users_segments = UserRelationship::setUsersAsSupervisor($users);

        return $this->success($users_segments ?? []);
    }

    public function setDataSupervisor(Request $request)
    {
//        Supervisor::setDataSupervisor($request->all());
        UserRelationship::setDataSupervisor($request->all());
        return $this->success([]);
    }

    public function destroy(User $supervisor)
    {
//        Supervisor::deleteSupervisor($supervisor);
        $segments = Segment::where('model_type', User::class)->where('model_id', $supervisor->id)
            ->whereRelation('code', 'code', 'user-supervise')
            ->get();

        foreach ($segments as $segment) {
            $segment->values()->delete();
            $segment->delete();
        }
        return $this->success(['msg' => 'Se eliminó el supervisor correctamente.']);
    }

    public function getData(User $supervisor, $type)
    {
        $data = UserRelationship::getDataToAssignSupervised($supervisor, $type);

        return $this->success($data);
    }

    public function tipoCriterios()
    {
        $workspace = get_current_workspace();

        $data = Criterion::query()
            ->whereHas('workspaces', function ($q) use ($workspace) {
                $q->where('id', $workspace->id);
            })
            ->select('name as nombre', 'id')->get();

        return $this->success($data);
    }

    public function setCriterioGlobalesSupervisor(Request $request)
    {
//        return $request->all();
        $data = $request->all();
        $users_id = array_column($data['supervisores'], 'id');
        $criteria = $data['tipo_criterios'];

//        dd($users_id, $criteria);
        $data = Supervisor::setCriterioGlobalesSupervisor($users_id, $criteria);

        return $this->success(compact('data'));
    }

    public function searchSupervisores(Request $request)
    {
        $data = Supervisor::searchSupervisores($request);
        return $this->success($data);
    }

    public function initData()
    {
        Taxonomy::firstOrCreate([
            'group' => 'segment',
            'type' => 'code',
            'code' => 'user-supervise',
            'name' => 'Supervisión de usuarios',
            'active' => ACTIVE,
            'position' => 1,
        ]);
    }

    public function searchInstructors(Request $request)
    {
        $workspace = get_current_workspace();
        $request->mergeIfMissing(['workspace' => $workspace?->id]);
        $request->mergeIfMissing(['code' => 'supervise']);

        $data = User::query()
            ->whereHas('segments')
            ->withCount([
                'segments' => function ($q) {
                    $q->
                    whereRelation('code', 'code', 'user-supervise');
                },
            ])
            ->whereRelation('segments.code', 'code', 'user-supervise');

        $data->withWhereHas('subworkspace', function ($query) use ($request) {
            if ($request->subworkspace)
                $query->whereIn('id', $request->subworkspace);
            else
                $query->where('parent_id', $request->workspace);
        });

        if ($request->has('filtro'))
            $data->filterText($request->filtro);

        $data = $data->select('id', 'document', 'name', 'lastname', 'surname')
                    ->limit(40)
                    ->get();

        return $this->success(['users' => $data]);
    }

}
