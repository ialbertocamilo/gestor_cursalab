<?php

namespace App\Http\Controllers\Induction;

use App\Http\Controllers\Controller;
use App\Http\Requests\Induction\StageStoreUpdateRequest;
use App\Models\Course;
use App\Models\Process;
use App\Models\School;
use App\Models\Stage;
use App\Models\Taxonomy;
use Illuminate\Http\Request;

class StageController extends Controller
{

    public function getFormSelects($compactResponse = false)
    {
        $qualification_types = Taxonomy::getDataForSelect('system', 'qualification-type');

        $response = compact('qualification_types');

        return $compactResponse ? $response : $this->success($response);
    }

    public function search(Process $process, Request $request)
    {
        $workspace = get_current_workspace();
        $request->mergeIfMissing([
            'workspace_id' => $workspace?->id,
            'process_id' => $process?->id
        ]);

        $data = Stage::getStagesList($request->all());

        return $this->success($data);
    }

    public function storeInline(Request $request)
    {
        $data = [ 'title' => $request->title ];

        $stage = Stage::storeRequest($data);

        $response = [
            'msg' => 'Etapa creada correctamente.',
            'stage' => $stage,
            'messages' => ['list' => []]
        ];
        return $this->success($response);
    }

    public function store(Process $process, StageStoreUpdateRequest $request)
    {
        $data = $request->validated();

        $data_school = [
            'name' => $data['title'],
            'active' => $data['active'],
            'subworkspaces' => current_subworkspaces_id()
        ];

        $school = School::storeRequest($data_school);

        $data['school_id'] = $school?->id ?? null;

        $stage = Stage::storeRequest($data);

        cache_clear_model(Stage::class);

        $response = [
            'msg' => 'Etapa creada correctamente.',
            'stage' => $stage,
            'messages' => ['list' => []]
        ];
        return $this->success($response);
    }

    public function update(Process $process, Stage $stage, StageStoreUpdateRequest $request)
    {
        $data = $request->validated();
        $data_school = [
            'name' => $data['title'],
            'active' => $data['active'],
            'subworkspaces' => current_subworkspaces_id()
        ];
        $school = School::where('id', $stage?->school_id)->first() ?? null;
        $school = School::storeRequest($data_school, $school);
        $data['school_id'] = $school?->id ?? null;

        $stage_result = Stage::storeRequest($data, $stage);

        cache_clear_model(Stage::class);

        $response = [
            'msg' => 'Etapa actualizada correctamente.',
            'stage' => $stage_result,
            'messages' => ['list' => []]
        ];
        return $this->success($response);
    }

    public function getData(Stage $stage)
    {
        $response = Stage::getData($stage);

        return $this->success($response);
    }
    /**
     * Stage request to toggle value of active status (1 or 0)
     *
     * @param Stage $stage
     * @param Request $request
     * @return JsonResponse
     */
    public function status(Process $process, Stage $stage, Request $request)
    {
        $stage->update(['active' => !$stage->active]);

        return $this->success(['msg' => 'Estado actualizado correctamente.']);
    }

    /**
     * Stage request to delete stage record
     *
     * @param Stage $stage
     * @return JsonResponse
     */
    public function destroy(Process $process, Stage $stage)
    {
        $stage->delete();

        return $this->success(['msg' => 'Etapa eliminada correctamente.']);
    }
}
