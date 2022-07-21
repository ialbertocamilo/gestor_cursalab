<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PollQuestionStoreRequest extends FormRequest
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
            'titulo' => 'required',
            'type_id' => 'required',
            'opciones' => 'nullable',
            'active' => 'required',
        ];
    }

    public function validationData()
    {
        $data = [];

        if ( ! $this->has('active') )
            $data['active'] = false;

        return $this->merge($data)->all();
    }

}
