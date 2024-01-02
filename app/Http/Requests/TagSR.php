<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TagSR extends FormRequest
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
        
        $reglas = [
            // 'nombre' => 'required|min:3',
            'name' => "required|min:3|max:20|unique:taxonomies,name,{$id},id",
            'description' => "max:120",
            'model_type' => "required",
            'color' => 'nullable',
        ];

        return $reglas;
    }

}
