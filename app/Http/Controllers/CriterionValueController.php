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
        $current_workspace_id = $request->workspace_id ?? session('workspace')['id'] ?? null;;
        $request->merge(['workspace_id'=> $current_workspace_id]);

        $criteria = CriterionValue::search($request);

        CriterionValueResource::collection($criteria);

        return $this->success($criteria);
    }

    public function getFormSelects(Criterion $criterion, $compactResponse = false)
    {
        $data_type = $criterion->field_type->code;

        $response = compact('data_type');

        return $compactResponse ? $data_type : $this->success($response);
    }

    public function store(CriterionValueStoreRequest $request, Criterion $criterion)
    {
        $data = $request->validated();
        $colum_name = CriterionValue::getCriterionValueColumnNameByCriterion($criterion);;
        $data[$colum_name] = $data['name'];

        CriterionValue::storeRequest($data);

        return $this->success(['msg' => 'Valor creado correctamente.']);
    }

    public function edit(Criterion $criterion, CriterionValue $criterion_value)
    {

        $column_name = $criterion_value->getCriterionValueColumnName();
        $criterion_value->name = $criterion_value->$column_name;
        $data_type = $criterion->field_type->code;

        return $this->success(get_defined_vars());
    }

    public function update(CriterionValueStoreRequest $request, Criterion $criterion, CriterionValue $criterion_value)
    {
        $data = $request->validated();
        $colum_name = $criterion_value->getCriterionValueColumnName();;
        $data[$colum_name] = $data['name'];

        CriterionValue::storeRequest($data, $criterion_value);

        return $this->success(['msg' => 'Valor actualizado correctamente.']);
    }


}
