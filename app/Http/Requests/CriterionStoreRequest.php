<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CriterionStoreRequest extends FormRequest
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
        $id = $this->method() == 'PUT' ? $this->segment(2) : 'NULL';

        $rules = [
            'name' => "required|max:150|min:3|unique:criteria,name,{$id},id",
            'field_id' => 'required',
            'position' => 'required',
            'multiple' => 'required',

            'workspace_id' => 'nullable'
        ];

        return $rules;
    }

    public function validationData()
    {
        $data = [];
        $multiple = ($this->multiple === 'true' or $this->multiple === true or $this->multiple === 1 or $this->multiple === '1');
        $workspace_id = $this->workspace_id ?? session('workspace')['id'] ?? null;

        $data['multiple'] = $multiple;
        $data['workspace_id'] = $workspace_id;

        return $this->merge($data)->all();
    }

}
