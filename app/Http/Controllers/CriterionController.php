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
        $workspace = get_current_workspace();
        $request->merge(['workspace_id'=> $workspace?->id]);

        $criteria = Criterion::search($request);

        CriterionResource::collection($criteria);

        return $this->success($criteria);
    }

    public function searchWk(Request $request)
    {
        // $workspace = get_current_workspace();
        // $request->merge(['workspace_id'=> $workspace?->id]);

        $criteria = Criterion::search($request);

        CriterionResource::collection($criteria);

        return $this->success($criteria);
    }

    public function getWorkspaceCriteria() {

        $workspace = get_current_workspace();
        return Criterion::getListForSelectWorskpace($workspace->id);
    }

    public function getFormSelects($compactResponse = false)
    {
        $data_types = Taxonomy::getDataForSelect('criterion', 'type');
        $default_position = SortingModel::getNextItemOrderNumber(Criterion::class, columnName: 'position');
        $response = compact('data_types', 'default_position');
        if(!$compactResponse){
            $criteria = Criterion::select('id','name')->where('active',1)->get();
            $response['criteria'] = $criteria;
        }
        return $compactResponse ? $data_types : $this->success($response);
    }

    public function edit(Criterion $criterion)
    {
        $data_types = Taxonomy::getDataForSelect('criterion', 'type');
        $criterion->default_position = SortingModel::getLastItemOrderNumber(Criterion::class, columnName: 'position');
        $criteria = Criterion::select('id','name')->where('active',1)->get();

        return $this->success(get_defined_vars());
    }

    public function store(CriterionStoreRequest $request)
    {
        $data = $request->validated();
//        info($data);
        $workspace = get_current_workspace();
        $data['workspace_id'] = $workspace?->id;

        Criterion::storeRequest($data);

        return $this->success(['msg' => 'Criterio creado correctamente.']);
    }

    public function update(CriterionStoreRequest $request, Criterion $criterion)
    {
        $data = $request->validated();
//        info($data);
        $workspace = get_current_workspace();
        $data['workspace_id'] = $workspace?->id;

//        $validations = Criterion::validationsOnUpdate($criterion, $data);
//
//        if (count($validations['list']) > 0)
//            return $this->success(compact('validations'), 'Ocurrió un error' , 422);

        Criterion::storeRequest($data, $criterion);

        return $this->success(['msg' => 'Criterio actualizado correctamente.']);
    }
}
