<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PreguntaStoreRequest extends FormRequest
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
                'pregunta' => 'required',
                // 'rptas_json' => 'required',
                // 'rpta_ok' => 'required'
            ];
        }
        else{
            $reglas = [
                'pregunta' => 'required',
                // 'rptas_json' => 'required',
                // 'rpta_ok' => 'required'
            ];
          }
          return $reglas;
    }

    public function messages()
    {
        return [
            'pregunta.required' => 'El dato "pregunta" es requerido',
            'rptas_json.required' => 'El dato "opciones" es requerido',
            'rpta_ok.required' => 'El dato "opción correcta" es requerido'
            
        ];
    }
}
