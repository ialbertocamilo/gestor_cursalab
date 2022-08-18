<?php

namespace App\Http\Controllers;

use App\Http\Requests\WorkspaceRequest;
use App\Http\Resources\WorkspaceResource;
use App\Models\CriterionValue;
use App\Models\Media;
use App\Models\Workspace;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        $workspace->load(['criteria', 'criteriaValue']);
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

        // Insert criterion values

        $criteria = json_decode($data['selected_criteria'], true);
        $criteriaIds = array_keys($criteria);
        $criterionValues = [];
        foreach ($criteriaIds as $criterionId) {
            if ($criteria[$criterionId]) {
                $criterionValues[] = [
                    'criterion_id' => $criterionId,
                    'active' => 1
                ];
            }
        }

        $criterionValuesIds = CriterionValue::bulkInsertAndGetIds($criterionValues);

        // Save workspace criteria values

        $workspaceCriteriaValue = [];
        foreach ($criterionValuesIds as $criterionValueId) {
            $workspaceCriteriaValue[] = [
              'workspace_id' => $workspace->id,
              'criterion_value_id' => $criterionValueId
            ];
        }

        $workspace->criteriaValue()->sync($workspaceCriteriaValue);

        // Response

        return $this->success(['msg' => 'Workspace actualizado correctamente.']);
    }
}
