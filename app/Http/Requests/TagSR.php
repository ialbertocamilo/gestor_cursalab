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
            'nombre' => "required|min:3|unique:tags,nombre,{$id},id",
            'color' => 'nullable',
        ];

        return $reglas;
    }

}
