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
            'name' => "required|min:3|unique:criterion_values,{$column_name},{$id},id",
            'criterion_id' => "required",
        ];
    }

    public function validationData()
    {
        $this->mergeIfMissing(['criterion_id' => $this->segment(2)]);


       return $this->all();
    }
//
    public function messages()
    {
        return [
            'name.unique' => 'El valor ya ha sido registrado.',
        ];
    }

}
