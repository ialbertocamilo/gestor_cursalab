<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubWorkspaceRequest extends FormRequest
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
            'name' => 'required',

            'codigo_matricula' => 'required',
            'active' => 'nullable',
            'mod_evaluaciones' => 'required',
            'reinicios_programado' => 'nullable',
            // 'reinicios_programado' => 'required',
            'app_menu' => 'nullable',

            'logo' => 'nullable',
            'file_logo' => 'nullable',
            'plantilla_diploma' => 'nullable',
            'file_plantilla_diploma' => 'nullable',
        ];
   
        return $reglas;
    }

    public function validationData()
    {
        $data['reinicios_programado'] = $this->reinicios_programado ? json_decode($this->reinicios_programado, true) : [];
        $data['mod_evaluaciones'] = $this->mod_evaluaciones ? json_decode($this->mod_evaluaciones, true) : [];

        return $this->merge($data)->all();
    }
}
