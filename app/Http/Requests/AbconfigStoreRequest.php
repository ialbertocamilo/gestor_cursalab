<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AbconfigStoreRequest extends FormRequest
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
                'etapa' => 'required',
//                'color'=>'required',
                'duracion_dias'=>'required',
                'ev_preg'=>'required',
                'ev_nota'=>'required',
                'ev_intento'=>'required'
            ];
        }
        else{
            $reglas = [
                'etapa' => 'required',
                // 'logo'=>'required|image|mimes:jpeg,png,jpg|max:5000',
                // 'plantilla_diploma'=>'required|image|mimes:jpeg,png,jpg|max:5000',
                'logo'=>'required',
                'plantilla_diploma'=>'required',
//                'color'=>'required',
                'duracion_dias'=>'required',
                'ev_preg'=>'required',
                'ev_nota'=>'required',
                'ev_intento'=>'required'

            ];
          }
          return $reglas;
    }
    public function messages()
    {
        return [
            'etapa.required' => 'El dato "etapa" es requerido',
            'logo.required' => 'El dato "logo" es requerido',
            'plantilla_diploma.required' => 'El dato "diploma" es requerido',
            'color.required' => 'El dato "color" es requerido',
            'duracion_dias.required' => 'El dato "duracion dias" es requerido',
            'ev_preg.required' => 'El dato "pregunta por evaluacion" es requerido',
            'ev_nota.required' => 'El dato "nota aprobatoria" es requerido',
            'ev_intento.required' => 'El dato "numero intentos" es requerido'

        ];
    }
}
