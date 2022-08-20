<?php

namespace App\Http\Controllers;

use App\Http\Requests\WorkspaceRequest;
use App\Http\Requests\SubWorkspaceRequest;
use App\Http\Resources\WorkspaceResource;
use App\Http\Resources\SubWorkspaceResource;

use App\Models\Criterion;
use App\Models\CriterionValue;
use App\Models\Media;
use App\Models\Workspace;
use App\Models\Taxonomy;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkspaceController extends Controller
{

    /**
     * Process request to render workspaces' selector view
     *
     * @return Application|Factory|View
     */
    public function list(): View|Factory|Application
    {

        return view(
            'workspaces.list',
            []
        );
    }

    /**
     * Process request to search workspaces
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {

        $workspaces = Workspace::search($request);
        WorkspaceResource::collection($workspaces);

        return $this->success($workspaces);
    }

    /**
     * Process request to load record data
     *
     * @param Workspace $workspace
     * @return JsonResponse
     */
    public function edit(Workspace $workspace): JsonResponse
    {
        // Load criteria

        $workspace['criteria'] = Criterion::where('active', ACTIVE)
                                          ->where('show_in_segmentation', 1)
                                          ->get();

        $workspace['criteria_workspace'] = CriterionValue::getCriteriaFromWorkspace($workspace->id);

        return $this->success($workspace);
    }

    /**
     * Process request to save record changes
     *
     * @param WorkspaceRequest $request
     * @param Workspace $workspace
     * @return JsonResponse
     */
    public function update(WorkspaceRequest $request, Workspace $workspace): JsonResponse
    {
        $data = $request->validated();

        // Upload files

        $data = Media::requestUploadFile($data, 'logo');
        $data = Media::requestUploadFile($data, 'logo_negativo');

        // Update record in database

        $workspace->update($data);


        // Save workspace's criteria

        $criteriaSelected = json_decode($data['selected_criteria'], true);
        $criteriaIds = array_keys($criteriaSelected);

        $workspaceCriteria = [];
        foreach ($criteriaIds as $criterionId) {
            if ($criteriaSelected[$criterionId]) {

                $workspaceCriteria[] = [
                    'workspace_id' => $workspace->id,
                    'criterion_id' => $criterionId
                ];
            }
        }

        DB::table('criterion_workspace')->where('workspace_id', $workspace->id)->delete();
        DB::table('criterion_workspace')->insert($workspaceCriteria);

        // Response

        return $this->success(['msg' => 'Workspace actualizado correctamente.']);
    }

    // Sub Workspaces

    public function searchSubWorkspace (Request $request)
    {
        $subworkspaces = Workspace::searchSubWorkspace($request);
        SubWorkspaceResource::collection($subworkspaces);

        return $this->success($subworkspaces);
    }

    public function destroy(Workspace $subworkspace)
    {
        // \File::delete(public_path().'/'.$subworkspace->plantilla_diploma);
        $subworkspace->delete();

        return back()->with('info', 'Eliminado Correctamente');
    }

    public function editSubWorkspace(Workspace $subworkspace)
    {
        // $workspace = Workspace::where('criterion_value_id', $subworkspace->id)->first();

        $reinicio_automatico = json_decode($subworkspace->reinicios_programado);
        $subworkspace->reinicio_automatico = $reinicio_automatico->activado ?? false;
        $subworkspace->reinicio_automatico_dias = $reinicio_automatico->reinicio_dias ?? 0;
        $subworkspace->reinicio_automatico_horas = $reinicio_automatico->reinicio_horas ?? 0;
        $subworkspace->reinicio_automatico_minutos = $reinicio_automatico->reinicio_minutos ?? 0;


        $subworkspace->load('main_menu');
        $subworkspace->load('side_menu');

        $formSelects = $this->getFormSelects(true);

        $formSelects['main_menu']->each(function ($item) use ($subworkspace) {
            $item->active = $subworkspace->main_menu->where('id', $item->id)->first() !== NULL;
        });

        $formSelects['side_menu']->each(function ($item) use ($subworkspace) {
            $item->active = $subworkspace->side_menu->where('id', $item->id)->first() !== NULL;
        });

        $evaluacion = json_decode($subworkspace->mod_evaluaciones);
        $subworkspace->preg_x_ev = $evaluacion->preg_x_ev ?? NULL;
        $subworkspace->nota_aprobatoria = $evaluacion->nota_aprobatoria ?? NULL;
        $subworkspace->nro_intentos = $evaluacion->nro_intentos ?? NULL;

        return $this->success([
            'modulo' => $subworkspace,
            'main_menu' => $formSelects['main_menu'],
            'side_menu' => $formSelects['side_menu'],
        ]);
    }

    public function getFormSelects($compactResponse = false)
    {
        $main_menu = Taxonomy::where('group', 'system')->where('type', 'main_menu')
                            ->select('id', 'name')
                            ->get();

        $main_menu->each(function ($item) {
            $item->active = false;
        });

        $side_menu = Taxonomy::where('group', 'system')->where('type', 'side_menu')
                            ->select('id', 'name')
                            ->get();

        $side_menu->each(function ($item) {
            $item->active = false;
        });

        $response = compact('main_menu', 'side_menu');

        return $compactResponse ? $response : $this->success($response);
    }

    public function storeSubWorkspace(SubWorkspaceRequest $request)
    {
        $data = $request->validated();
        $data = Media::requestUploadFile($data, 'logo');
        $data = Media::requestUploadFile($data, 'plantilla_diploma');

        $subworkspace = Workspace::storeSubWorkspaceRequest($data);

        return $this->success(['msg' => 'Módulo registrado correctamente.']);
    }

    public function updateSubWorkspace(SubWorkspaceRequest $request, Workspace $subworkspace)
    {
        $data = $request->validated();
        $data = Media::requestUploadFile($data, 'logo');
        $data = Media::requestUploadFile($data, 'plantilla_diploma');

        $subworkspace = Workspace::storeSubWorkspaceRequest($data, $subworkspace);

        return $this->success(['msg' => 'Módulo actualizado correctamente.']);
    }
}
