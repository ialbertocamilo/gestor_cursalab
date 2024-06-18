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
            'active_results' => 'required',
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
            'validate' => 'required',
            'path_qr' => 'nullable',
            'qualification_type_id' => 'nullable',
            'modality_in_person_properties' => 'nullable',
            'tags' => 'nullable',
            'review_all_duration_media' => 'nullable',
            'open_evaluation_button' => 'nullable',
            'type_requirement' => 'nullable'
        ];
    }

    public function validationData()
    {
        $create = ($this->method() == 'POST');
        $data = [];

        // Al crear un Tema calificado, no se podrá activar hasta que se agregue una evaluación

        $active = ($this->active === 'true' or $this->active === true or $this->active === 1 or $this->active === '1');
        $data['active'] = $active;

        $active_results = ($this->active_results === 'true' or $this->active_results === true or $this->active_results === 1 or $this->active_results === '1');
        $data['active_results'] = $active_results;

         $data['review_all_duration_media'] = ($this->review_all_duration_media === 'true' or $this->review_all_duration_media === true or $this->review_all_duration_media === 1 or $this->review_all_duration_media === '1');

       if ( ! $this->has('assessable') )
            $data['assessable'] = 0;

        $data['qualification_type_id'] = $this->has('qualification_type') ? $this->qualification_type : null;
        $data['modality_in_person_properties'] = $this->modality_in_person_properties ? json_decode($this->modality_in_person_properties, true) : [];
        return $this->merge($data)->all();
    }
}
