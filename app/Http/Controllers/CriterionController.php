<?php

namespace App\Http\Controllers;

use App\Http\Requests\CriterionStoreRequest;
use App\Http\Resources\CriterionResource;
use App\Models\Criterion;
use App\Models\SortingModel;
use App\Models\Taxonomy;
use Illuminate\Http\Request;

class CriterionController extends Controller
{
    public function search(Request $request)
    {
//        $current_workspace = session('workspace');
        $current_workspace_id = $request->workspace_id ?? session('workspace')['id'] ?? null;
        $request->merge(['workspace_id'=> $current_workspace_id]);

        $criteria = Criterion::search($request);

        CriterionResource::collection($criteria);

        return $this->success($criteria);
    }

    public function getFormSelects($compactResponse = false)
    {
        $data_types = Taxonomy::getDataForSelect('criterion', 'type');
        $default_position = SortingModel::getNextItemOrderNumber(Criterion::class, columnName: 'position');

        $response = compact('data_types', 'default_position');

        return $compactResponse ? $data_types : $this->success($response);
    }

    public function edit(Criterion $criterion)
    {
        $data_types = Taxonomy::getDataForSelect('criterion', 'type');
        $criterion->default_position = SortingModel::getLastItemOrderNumber(Criterion::class, columnName: 'position');

        return $this->success(get_defined_vars());
    }

    public function store(CriterionStoreRequest $request)
    {
        $data = $request->validated();

        Criterion::storeRequest($data);

        return $this->success(['msg' => 'Criterio creado correctamente.']);
    }

    public function update(CriterionStoreRequest $request, Criterion $criterion)
    {
        $data = $request->validated();

//        $validations = Criterion::validationsOnUpdate($criterion, $data);
//
//        if (count($validations['list']) > 0)
//            return $this->success(compact('validations'), 'Ocurrió un error' , 422);

        Criterion::storeRequest($data, $criterion);

        return $this->success(['msg' => 'Criterio actualizado correctamente.']);
    }
}
