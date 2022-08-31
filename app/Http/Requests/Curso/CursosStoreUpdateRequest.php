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
            'name' => 'required',
            'description' => 'nullable',
            'position' => 'nullable',
            'active' => 'required',
            'requisito_id' => 'nullable',

            'reinicios_programado' => 'nullable',
            'lista_escuelas' =>  'required',

            'duration' => 'nullable' ,
            'investment' => 'nullable',

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

        $data['active'] = $active;
        $data['validateForm'] = !!$this->validateForm;
        $data['reinicios_programado'] = $this->reinicios_programado ? json_decode($this->reinicios_programado, true) : [];

        return $this->merge($data)->all();
    }
}
