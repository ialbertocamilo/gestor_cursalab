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
            'name' => "required|min:3|max:20|unique:taxonomies,name,{$id},id,group,tags",
            'description' => "max:120",
            'type' => 'required',
        ];

        return $reglas;
    }

    public function messages()
    {
        return [
            'name.unique' => 'Ya existe un tag con el mismo nombre',
        ];
    }

}
