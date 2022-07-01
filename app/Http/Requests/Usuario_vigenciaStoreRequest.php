<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Usuario_vigenciaStoreRequest extends FormRequest
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
                'usuario_id' => 'required|min:2',
                'fecha_inicio' => 'required',
                'fecha_fin' => 'required'
                
            ];
        }
        else{
            $reglas = [
              //
            ];
          }
          return $reglas;
    }

    public function messages()
    {
        return [
            'usuario_id.required' => 'El dato "usuario" es requerido',
            'fecha_inicio.required' => 'El dato "fecha inicio " es requerido',
            'fecha_fin.required' => 'El dato "fecha fin" es requerido'
            
            
        ];
    }
}
