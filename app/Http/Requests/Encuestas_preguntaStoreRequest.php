<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Encuestas_preguntaStoreRequest extends FormRequest
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
            // 'encuesta_id' => 'required',
            'titulo' => 'required',
            'tipo_pregunta' => 'required',
            'opciones' => 'nullable',
            'estado' => 'required',
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

}
