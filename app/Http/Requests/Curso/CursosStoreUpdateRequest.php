<?php

namespace App\Http\Requests\Curso;

use Illuminate\Foundation\Http\FormRequest;

class CursosStoreUpdateRequest extends FormRequest
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
        return [
            'name' => 'required|max:120',
            'description' => 'nullable',
            'position' => 'nullable',
            'active' => 'required',
            'show_certification_date' => 'required',
            'requisito_id' => 'nullable',

            'reinicios_programado' => 'nullable',
            'mod_evaluaciones' => 'nullable',

            'lista_escuelas' =>  'required',

            'duration' => 'nullable' ,
            'investment' => 'nullable',
            'type_id' => 'nullable',

            'imagen' => 'nullable',
            'plantilla_diploma' => 'nullable',
            'file_imagen' => 'nullable',
            'file_plantilla_diploma' => 'nullable',
            'validateForm' => 'required',
        ];
    }

    public function validationData()
    {
        $active = ($this->active === 'true' or $this->active === true or $this->active === 1 or $this->active === '1');
        $show_certification_date = ($this->show_certification_date === 'true' or $this->show_certification_date === true or $this->show_certification_date === 1 or $this->show_certification_date === '1');

        $data['active'] = $active;
        $data['show_certification_date'] = $show_certification_date;
        $data['validateForm'] = !!$this->validateForm;
        $data['reinicios_programado'] = $this->reinicios_programado ? json_decode($this->reinicios_programado, true) : [];
        $data['mod_evaluaciones'] = $this->mod_evaluaciones ? json_decode($this->mod_evaluaciones, true) : [];

        return $this->merge($data)->all();
    }
}
