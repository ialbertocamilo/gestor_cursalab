<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CriterioStoreRequest extends FormRequest
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
        $id = $this->method() == 'PUT' ? $this->segment(3) : 'NULL';
        $config_id = $this->modulo;

        $reglas = [
            'valor' => "required|min:3|unique:criterios,valor,{$id},id,config_id,{$config_id}",
            'modulo' => 'required',
            'config_id' => 'required',
        ];

        return $reglas;
    }

    public function validationData()
    {
        $data = [];

        if ( $this->has('modulo') )
            $data['config_id'] = $this->modulo;

        return $this->merge($data)->all();
    }

    public function attributes()
    {
        return [
            'valor' => 'nombre',
            'modulo' => 'módulo',
            'config_id' => 'módulo',
        ];
    }

}
