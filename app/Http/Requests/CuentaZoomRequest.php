<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CuentaZoomRequest extends FormRequest
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
            'usuario' => 'required|min:3|max:255',
            'correo' => 'required|min:3|max:255',
            'zoom_userid' => 'required|min:3|max:255',
            'pmi' => 'required|min:3|max:255',
            'tipo' => 'required',
            'api_key' => 'required|min:3|max:255',
            'client_secret' => 'required|min:3|max:255',

            'estado' => 'nullable',

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
