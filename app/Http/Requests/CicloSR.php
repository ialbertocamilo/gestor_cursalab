<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CicloSR extends FormRequest
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
                'nombre' => 'required|min:3',
                'secuencia' => 'required|numeric',
            ];
        }
        else{
            $reglas = [
                'nombre' => 'required|min:3',
                'secuencia' => 'required|numeric',
                // 'imagen'=>'required|image|mimes:jpeg,png,jpg|max:5000',
                // 'archivo' => 'required|max:5000'
            ];
          }
          return $reglas;
    }

    public function messages()
    {
        return [
            'nombre.required' => 'El dato "nombre" es requerido',
            'imagen.required' => 'El dato "imagen" es requerido',
            
        ];
    }
}
