<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use App\Criterio;

class BoticaStoreRequest extends FormRequest
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
        
        $config_id = $this->modulo;
        $criterio_id = $this->grupo;

        $reglas = [
            'nombre' => "required|min:3|unique:boticas,nombre,{$id},id,config_id,{$config_id},criterio_id,{$criterio_id}",
            'modulo' => 'required',
            'grupo' => 'required',
            'grupo_nombre' => 'nullable',
            'codigo_local' => 'required',
            'config_id' => 'required',
            'criterio_id' => 'required',
        ];

        return $reglas;
    }

    public function validationData()
    {
        $data = [];

        if ( $this->has('modulo') )
            $data['config_id'] = $this->modulo;

        if ( $this->has('grupo') )
        {
            $data['criterio_id'] = $this->grupo;

            $grupo = Criterio::find($this->grupo);

            $data['grupo_nombre'] = $grupo->valor ?? '';
        }

        return $this->merge($data)->all();
    }

}
