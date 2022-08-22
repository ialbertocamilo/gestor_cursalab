<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PollStoreRequest extends FormRequest
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
            'titulo' => 'required|min:3',
            'imagen' => 'nullable',
            'file_imagen' => 'nullable',
            'active' => 'nullable',
            'type_id' => 'nullable',
            'anonima' => 'nullable',
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
