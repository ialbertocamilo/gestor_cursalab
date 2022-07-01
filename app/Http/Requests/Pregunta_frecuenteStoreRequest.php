<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Pregunta_frecuenteStoreRequest extends FormRequest
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
        $reglas = [
            'pregunta' => 'required|min:3',
            'respuesta' => 'required|min:3',
            'estado' => 'required',
            'orden' => 'nullable',
        ];

        return $reglas;
    }

    public function validationData()
    {
        $data = [];

        if ( ! $this->has('estado') )
            $data['estado'] = false;

        return $this->merge($data)->all();
    }


    // public function messages()
    // {
    //     return [
    //         'pregunta.required' => 'El dato "pregunta" es requerido',
    //         'respuesta.required' => 'El dato "respuesta" es requerido'
    //     ];
    // }

}
