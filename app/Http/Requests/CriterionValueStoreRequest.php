<?php

namespace App\Http\Requests;

use App\Models\CriterionValue;
use Illuminate\Foundation\Http\FormRequest;

class CriterionValueStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $id = $this->method() == 'PUT' ? $this->segment(4) : 'NULL';
        $column_name = CriterionValue::getCriterionValueColumnNameByCriterion($this->criterion);

        return [
            'name' => "required|min:1|unique:criterion_values,{$column_name},{$id},id,criterion_id,{$this->criterion_id}",
            'criterion_id' => "required",



            'workspace_id' => "nullable",
        ];
    }

    public function validationData()
    {
        $this->mergeIfMissing(['criterion_id' => $this->segment(2)]);

        $workspace_id = $this->workspace_id ?? session('workspace')['id'] ?? null;
        $data['workspace_id'] = $workspace_id;

        return $this->merge($data)->all();
    }
//
    public function messages()
    {
        return [
            'name.unique' => 'El valor ya ha sido registrado.',
        ];
    }

}
