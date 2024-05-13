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
            // 'mod_evaluaciones' => 'required',
            'reinicios_programado' => 'nullable',
            'contact_support' => 'nullable',
            // 'reinicios_programado' => 'required',
            'app_menu' => 'nullable',

            'logo' => 'nullable',
            'file_logo' => 'nullable',
            'plantilla_diploma' => 'nullable',
            'file_plantilla_diploma' => 'nullable',
            'certificate_template_id' => 'nullable',
            'show_logo_in_app' => 'nullable',
            'registro_capacitacion' => 'nullable',
            'benefits_configuration' => 'nullable'
        ];

        return $reglas;
    }

    public function validationData()
    {
        $data['reinicios_programado'] = $this->reinicios_programado ? json_decode($this->reinicios_programado, true) : [];
        $data['registro_capacitacion'] = $this->registro_capacitacion ? json_decode($this->registro_capacitacion, true) : [];
        $data['benefits_configuration'] = $this->benefits_configuration ? json_decode($this->benefits_configuration, true) : [];
        $data['mod_evaluaciones'] = $this->mod_evaluaciones ? json_decode($this->mod_evaluaciones, true) : [];
        $data['contact_support'] = $this->contact_support ? json_decode($this->contact_support, true) : [];

        if (auth()->user()->isA('super-user') && $this->has('show_logo_in_app') ) {
            $data['show_logo_in_app'] = ($this->show_logo_in_app == 'true' || $this->show_logo_in_app == 1 ) ? true : false;
        }

        return $this->merge($data)->all();
    }
}
