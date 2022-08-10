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
            'name' => 'required',
            'description' => 'nullable',
            'content' => 'nullable',
            'active' => 'required',
            'position' => 'required',
            'categoria_id' => 'nullable',
            'course_id' => 'required',

            //            'media' => 'nullable',

            'new_medias' => 'nullable',
            'medias' => 'nullable',

            'imagen' => 'nullable',
            'file_imagen' => 'nullable',

            'assessable' => 'required',
            'type_evaluation_id ' => 'nullable',
            'topic_requirement_id ' => 'nullable',
            'tags' => 'nullable',
            'check_tipo_ev' => 'nullable'

        ];
    }

    public function validationData()
    {
        $active = ($this->active === 'true' or $this->active === true or $this->active === 1 or $this->active === '1');

        $data['active'] = $active;

        return $this->merge($data)->all();
    }
}
