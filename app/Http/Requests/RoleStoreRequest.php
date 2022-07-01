<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoleStoreRequest extends FormRequest
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
       if ($this->method() == 'PUT'){
            $reglas = [
                'name' => 'required',
                'slug' => 'required',
                'description' => 'required'
            ];
        }
        else{
            $reglas = [
                'name' => 'required',
                'slug' => 'required',
                'description' => 'required'
            ];
          }
          return $reglas;
    }

    public function messages()
    {
        return [
            'name.required' => 'El dato "nombre" es requerido',
            'slug.required' => 'El dato "url amigable" es requerido',
            'description.required' => 'El dato "descripción" es requerido'
            
        ];
    }
}
