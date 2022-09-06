<?php

namespace App\Http\Requests\Tema;

use Illuminate\Foundation\Http\FormRequest;

class TemaStoreUpdateRequest extends FormRequest
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
            'content' => 'nullable',
            'active' => 'required',
            'position' => 'required',
            'categoria_id' => 'nullable',
            'course_id' => 'required',

            'assessable' => 'nullable',
            'type_evaluation_id' => 'nullable',
            'topic_requirement_id' => 'nullable',
            'check_tipo_ev' => 'nullable',

            'new_medias' => 'nullable',
            'medias' => 'nullable',

            'imagen' => 'nullable',
            'file_imagen' => 'nullable',
            'validate' => 'required'
        ];
    }

    public function validationData()
    {
        $create = ($this->method() == 'POST');
        $data = [];

        // Al crear un Tema calificado, no se podrá activar hasta que se agregue una evaluación

//        $active = ($this->active === 'true' or $this->active === true or $this->active === 1 or $this->active === '1');
//
       if ( ! $this->has('assessable') )
            $data['assessable'] = 0;

        return $this->merge($data)->all();
    }
}
