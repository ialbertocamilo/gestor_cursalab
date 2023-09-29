<?php

namespace App\Http\Controllers;

use App\Http\Requests\CriterionValueStoreRequest;
use App\Http\Resources\CriterionValueResource;
use App\Models\Criterion;
use App\Models\CriterionValue;
use Illuminate\Http\Request;

class CriterionValueController extends Controller
{
    public function search(Request $request, Criterion $criterion)
    {
        $request->merge(['criterion_id' => $criterion->id]);
        $current_workspace_id = get_current_workspace();
        $request->merge(['workspace_id' => $current_workspace_id?->id]);

        $criteria = CriterionValue::search($request);

        CriterionValueResource::collection($criteria);

        return $this->success($criteria);
    }

    public function searchWk(Request $request, Criterion $criterion)
    {
        $request->merge(['criterion_id' => $criterion->id]);
        // $current_workspace_id = get_current_workspace();
        // $request->merge(['workspace_id' => $current_workspace_id?->id]);

        $criteria = CriterionValue::search($request);

        CriterionValueResource::collection($criteria);

        return $this->success($criteria);
    }

    public function getFormSelects(Criterion $criterion, $compactResponse = false)
    {
        $data_type = $criterion->field_type->code;

        $response = compact('data_type');
        if(!$compactResponse && $criterion->parent_id){
            $response['criterion_values_parent'] = $this->getCriterionValuesParentByWorkspace($criterion);
        }
        return $compactResponse ? $data_type : $this->success($response);
    }
    private function getCriterionValuesParentByWorkspace($criterion){
        return CriterionValue::select('id','value_text')
            ->where('criterion_id',$criterion->parent_id)
            ->whereRelation('workspaces', 'id', get_current_workspace()->id)
            ->get();
    }
    public function store(CriterionValueStoreRequest $request, Criterion $criterion)
    {
        $data = $request->validated();
        $current_workspace_id = get_current_workspace();
        $data['workspace_id'] = $current_workspace_id?->id;

        $colum_name = CriterionValue::getCriterionValueColumnNameByCriterion($criterion);
        $data[$colum_name] = $data['name'];
        $data['value_text'] = $data['name'];

        CriterionValue::storeRequest($data);

        return $this->success(['msg' => 'Valor creado correctamente.']);
    }

    public function edit(Criterion $criterion, CriterionValue $criterion_value)
    {

        $column_name = $criterion_value->getCriterionValueColumnName();
        $criterion_value->name = $criterion_value->$column_name;
        $data_type = $criterion->field_type->code;
        if($criterion->parent_id){
            $criterion_values_parent =  $this->getCriterionValuesParentByWorkspace($criterion);
        }
        return $this->success(get_defined_vars());
    }

    public function update(CriterionValueStoreRequest $request, Criterion $criterion, CriterionValue $criterion_value)
    {
        $data = $request->validated();
        $current_workspace_id = get_current_workspace();
        $data['workspace_id'] = $current_workspace_id?->id;

        $colum_name = $criterion_value->getCriterionValueColumnName();;
        $data[$colum_name] = $data['name'];
        $data['value_text'] = $data['name'];

        CriterionValue::storeRequest($data, $criterion_value);

        return $this->success(['msg' => 'Valor actualizado correctamente.']);
    }
}
