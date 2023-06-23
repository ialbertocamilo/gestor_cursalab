<?php

namespace App\Http\Requests\Benefit;

use Illuminate\Foundation\Http\FormRequest;

class BenefitStoreUpdateRequest extends FormRequest
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
            'title' => 'required|max:120',
            'description' => 'required',
            'active' => 'required',
            'correo' => 'required',
            'cupos' => 'required',
            'fin_inscripcion' => 'required',
            'inicio_inscripcion' => 'required',
            'fecha_liberacion' => 'required',
            'type' => 'required',

            // 'position' => 'nullable',
            // 'type_id' => 'nullable',
            'dificultad' => 'nullable',
            'discapacidad' => 'nullable',
            'list_links' => 'nullable',
            'lista_encuestas' => 'nullable',
            'list_silabos' => 'nullable',
            'promotor' => 'nullable',
            'referencia' => 'nullable',
            'speaker' => 'nullable',
            'ubicacion_mapa' => 'nullable',


            'image' => 'nullable',
            'file_image' => 'nullable',
            'validateForm' => 'required',
        ];
    }

    public function validationData()
    {
        $active = ($this->active === 'true' or $this->active === true or $this->active === 1 or $this->active === '1');

        $data['active'] = $active;
        $data['validateForm'] = !!$this->validateForm;

        return $this->merge($data)->all();
    }
}
