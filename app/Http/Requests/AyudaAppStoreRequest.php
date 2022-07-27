<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AyudaAppStoreRequest extends FormRequest
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
            // 'imagen'=>'required|image|mimes:jpeg,png,jpg|max:5000'
            'title' => 'required|max:250',
            'position' => 'nullable',
            'check_text_area' => 'required',
        ];

        return $reglas;
    }

    public function validationData()
    {
        $data = [];

        if ( ! $this->has('check_text_area') )
            $data['check_text_area'] = false;

        return $this->merge($data)->all();
    }
}
