<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\School;
use App\Models\Abconfig;
use App\Models\Categoria;
use App\Models\Workspace;
use App\Models\SortingModel;
use Illuminate\Http\Request;
use App\Models\SchoolSubworkspace;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\Escuela\EscuelaSearchResource;
use App\Http\Requests\Escuela\EscuelaStoreUpdateRequest;

class EscuelaController extends Controller
{
    public function search(Workspace $abconfig, Request $request)
    {
        // $workspace = session('workspace');
        // $workspace_id = (is_array($workspace)) ? $workspace['id'] : null;

        // $request->workspace_id = $workspace_id;
        $request->canChangePosition =   boolval(
                                        isset($request->modules) 
                                        && count($request->modules) == 1 
                                        && !isset($request->active)
                                        && !isset($request->dates)
                                    );
        $escuelas = School::search($request);
        EscuelaSearchResource::collection($escuelas);

        return $this->success($escuelas);
    }

    public function searchCategoria(School $school)
    {
        $reinicio_automatico = json_decode($school->scheduled_restarts);
        $school->reinicio_automatico = $reinicio_automatico->activado ?? false;
        $school->reinicio_automatico_dias = $reinicio_automatico->reinicio_dias ?? 0;
        $school->reinicio_automatico_horas = $reinicio_automatico->reinicio_horas ?? 0;
        $school->reinicio_automatico_minutos = $reinicio_automatico->reinicio_minutos ?? 0;
        // $nombre_ciclo_0 = DB::table('nombre_schools')->where('school_id', $school->id)->first();
        // $school->nombre_ciclo_0 = $nombre_ciclo_0->nombre ?? null;
        $school->makeHidden('scheduled_restarts');

        $school->load('subworkspaces');

        $workspace = get_current_workspace();

        $modules = Workspace::where('parent_id', $workspace?->id)
            ->select('id', 'name')->get();

        return $this->success([
            'escuela' => $school,
            'modules' => $modules,
        ]);
    }

    public function getFormSelects($compactResponse = false)
    {
        $workspace = get_current_workspace();

        $modules = Workspace::where('parent_id', $workspace?->id)
            ->select('id', 'name')->get();

        return $compactResponse ? [] : $this->success(compact('modules'));
    }

    public function store(EscuelaStoreUpdateRequest $request)
    {
        $data = $request->validated();
        $data = Media::requestUploadFile($data, 'imagen');
        $data = Media::requestUploadFile($data, 'plantilla_diploma');

        $escuela = School::storeRequest($data);

        $msg = 'Escuela creado correctamente.';
        return $this->success(compact('escuela', 'msg'));
    }

    public function update(EscuelaStoreUpdateRequest $request, School $school)
    {
        $data = $request->validated();
        $data = Media::requestUploadFile($data, 'imagen');
        $data = Media::requestUploadFile($data, 'plantilla_diploma');

        School::storeRequest($data, $school);

        $msg = 'Escuela actualizada correctamente.';

        return $this->success(compact('school', 'msg'));
    }

    public function destroyEscuela(School $school)
    {
        // $validate = Categoria::validateEscuelaEliminar($school);

        if ($school->courses()->count() > 0)
            return $this->error('La escuela tiene cursos.', 422, [['Para eliminar la escuela no debe tener cursos.']]);

        SortingModel::deletePositionInPivotTable(SchoolSubworkspace::class,School::class,[
            'school_id' => $school->id
        ]);
        $school->delete();
        return $this->success(['msg' => 'Escuela eliminada correctamente.']);
    }

    // public function updateStatus(Abconfig $abconfig, Categoria $categoria)
    // {
    //     //        return $categoria;

    //     $estado = ($categoria->estado == 1) ? 0 : 1;
    //     $categoria->estado = $estado;
    //     $categoria->save();
    //     return $this->success(['info' => 'Estado actualizado con éxito.']);
    // }

    public function copy(School $school)
    {
        $items = School::getCoursesForTree($school->courses);

        $items_destiny = School::getAvailableForTree($school);

        return $this->success(compact('school', 'items', 'items_destiny'));
    }

}
