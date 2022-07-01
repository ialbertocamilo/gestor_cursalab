<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TipoCriterioStoreRequest extends FormRequest
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
            'nombre' => "required|max:150|min:3|unique:tipo_criterios,nombre,{$id},id",
            // 'nombre' => 'required',
            'nombre_plural' => 'required',
            'data_type' => 'required',
            'orden' => 'required',
            'obligatorio' => 'required',
        ];

        return $reglas;
    }

    public function validationData()
    {
        $data = [];

        if ( ! $this->has('obligatorio') )
            $data['obligatorio'] = false;

        return $this->merge($data)->all();
    }

}
