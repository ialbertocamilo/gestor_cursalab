<?php

namespace App\Http\Controllers;

use App\Console\Commands\reinicios_programado;
use App\Http\Requests\WorkspaceRequest;
use App\Http\Requests\SubWorkspaceRequest;
use App\Http\Resources\WorkspaceResource;
use App\Http\Resources\SubWorkspaceResource;

use App\Models\Criterion;
use App\Models\CriterionValue;
use App\Models\Media;
use App\Models\SegmentValue;
use App\Models\User;
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
    public function list_criterios(): View|Factory|Application
    {
        return view(
            'criteria.list_in_wk',
            []
        );
    }
    public function list_criterios_values(): View|Factory|Application
    {
        return view(
            'criterion_values.list_in_wk',
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

        $workspace['criteria'] = Criterion::where('active', ACTIVE)->get();

        foreach ($workspace['criteria'] as $wk_crit) {
            $in_segment = SegmentValue::where('criterion_id', $wk_crit->id)->get();
            $in_segment_list = $in_segment->pluck('id')->all();
            $wk_crit->its_used = false;
            if (count($in_segment_list))
                $wk_crit->its_used = true;
        }

        // $workspace['criteria_workspace'] = CriterionValue::getCriteriaFromWorkspace($workspace->id);
        $workspace['criteria_workspace'] = $workspace->criterionWorkspace->toArray();

        $workspace['limit_allowed_users'] = $workspace->limit_allowed_users['quantity'] ?? null;

        // $workspace['is_superuser'] = auth()->user()->isA('super-user');
        $workspace['is_superuser'] = true;

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
        info(['data' => $request->all() ]);

        $data = Media::requestUploadFile($data, 'logo');
        $data = Media::requestUploadFile($data, 'logo_negativo');
        $data = Media::requestUploadFile($data, 'logo_marca_agua');

        // Set constraint: limit allowed users

        if (($data['limit_allowed_users_type'] ?? false) && ($data['limit_allowed_users_limit'] ?? false)):

            $constraint_user['type'] = $data['limit_allowed_users_type'];
            $constraint_user['quantity'] = intval($data['limit_allowed_users_limit']);

            $data['limit_allowed_users'] = $constraint_user;
        else:
            $data['limit_allowed_users'] = null;
        endif;

        // Update record in database

        $workspace->update($data);

        // Save workspace's criteria

        $criteriaSelected = json_decode($data['selected_criteria'], true);

        $criteria = [];

        $module_criterion = Criterion::where('code', 'module')->first();

        foreach ($criteriaSelected as $criterion_id => $is_selected) {
            if ($is_selected) $criteria[] = $criterion_id;
        }

        $criteria[] = $module_criterion->id;

        $workspace->criterionWorkspace()->sync($criteria);

        \Artisan::call('modelCache:clear', array('--model' => "App\Models\Criterion"));

        return $this->success(['msg' => 'Workspace actualizado correctamente.']);
    }

    // Sub Workspaces

    public function searchSubWorkspace(Request $request)
    {
        $subworkspaces = Workspace::searchSubWorkspace($request);
        SubWorkspaceResource::collection($subworkspaces);

        return $this->success($subworkspaces);
    }

    public function getListSubworkspaceSelects()
    {
        $current_workspace = get_current_workspace();

        $active_users_count = User::onlyClientUsers()->whereRelation('subworkspace', 'parent_id', $current_workspace->id)
            ->where('active', 1)->count();
        $limit_allowed_users = $current_workspace->getLimitAllowedUsers();

        return $this->success(compact('active_users_count', 'limit_allowed_users'));
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

        $reinicio_automatico = $subworkspace->reinicios_programado;
        $subworkspace->reinicio_automatico = $reinicio_automatico['activado'] ?? false;
        $subworkspace->reinicio_automatico_dias = $reinicio_automatico['reinicio_dias'] ?? 0;
        $subworkspace->reinicio_automatico_horas = $reinicio_automatico['reinicio_horas'] ?? 0;
        $subworkspace->reinicio_automatico_minutos = $reinicio_automatico['reinicio_minutos'] ?? 0;


        $subworkspace->load('main_menu');
        $subworkspace->load('side_menu');

        $formSelects = $this->getFormSelects(true);

        $formSelects['main_menu']->each(function ($item) use ($subworkspace) {
            $item->active = $subworkspace->main_menu->where('id', $item->id)->first() !== NULL;
        });

        $formSelects['side_menu']->each(function ($item) use ($subworkspace) {
            $item->active = $subworkspace->side_menu->where('id', $item->id)->first() !== NULL;
        });

        $evaluacion = $subworkspace->mod_evaluaciones;
        $subworkspace->preg_x_ev = $evaluacion['preg_x_ev'] ?? NULL;
        $subworkspace->nota_aprobatoria = $evaluacion['nota_aprobatoria'] ?? NULL;
        $subworkspace->nro_intentos = $evaluacion['nro_intentos'] ?? NULL;

        $contact_support = $subworkspace->contact_support;
        $subworkspace->contact_phone = $contact_support['contact_phone'] ?? NULL;
        $subworkspace->contact_email = $contact_support['contact_email'] ?? NULL;
        $subworkspace->contact_schedule = $contact_support['contact_schedule'] ?? NULL;

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
            ->where('active', ACTIVE)
            ->get();

        $main_menu->each(function ($item) {
            $item->active = false;
        });

        $side_menu = Taxonomy::select('id', 'name')
                             ->where('group', 'system')
                             ->where('type', 'side_menu')
                             ->where('active', ACTIVE);

        #=== visible glossary only for FP ===
        $jump_menu = (get_current_workspace()->id !== 25);
        if($jump_menu) $side_menu->whereNotIn('name', ['Glosario']);
        #=== visible glossary only for FP ===

        $side_menu = $side_menu->get();

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
