<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FaqsStoreRequest extends FormRequest
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
            'title' => 'required|min:3',
            'content' => 'required|min:3',
            'active' => 'required',
            'position' => 'nullable',
        ];
    }

    public function validationData()
    {
        $data = [];

        if ( ! $this->has('active') )
            $data['active'] = false;

        return $this->merge($data)->all();
    }


    // public function messages()
    // {
    //     return [
    //         'pregunta.required' => 'El dato "pregunta" es requerido',
    //         'respuesta.required' => 'El dato "respuesta" es requerido'
    //     ];
    // }

}
