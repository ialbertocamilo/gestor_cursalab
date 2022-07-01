<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GlosarioStoreRequest extends FormRequest
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
        $id = $this->segment(2);

        return [
            'nombre' => "required|max:255|unique:glosarios,nombre,{$id},id,deleted_at,NULL",
            'categoria' => 'nullable',
            // 'categoria' => 'required|exists:taxonomias,id,grupo,glosario,tipo,categoria,deleted_at,NULL',
            'jerarquia' => 'nullable',
            'laboratorio' => 'nullable',
            'advertencias' => 'nullable',
            'condicion_de_venta' => 'nullable',
            'via_de_administracion' => 'nullable',
            'grupo_farmacologico' => 'nullable',
            'dosis_adulto' => 'nullable',
            'dosis_nino' => 'nullable',
            'recomendacion_de_administracion' => 'nullable',

            'modulos' => 'nullable',

            'principios_activos' => 'nullable',
            'contraindicaciones' => 'nullable',
            'interacciones' => 'nullable',
            'reacciones' => 'nullable',

            'estado' => 'required',
        ];
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
    //         'palabra.required' => 'El dato "palabra" es requerido',
    //         'descripcion.required' => 'El dato "descripción" es requerido'
    //     ];
    // }
}
