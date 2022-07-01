<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EncuestaStoreRequest extends FormRequest
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
            'titulo' => 'required|min:3',
            // 'imagen'=>'required|image|mimes:jpeg,png,jpg|max:5000'
            'imagen' => 'nullable',
            'file_imagen' => 'nullable',
            
            'estado' => 'nullable',
            'tipo' => 'nullable',
            'anonima' => 'nullable',
        ];

        return $reglas;
    }

    public function validationData()
    {
        $data = [];

        if ( ! $this->has('estado') )
            $data['estado'] = false;

        return $this->merge($data)->all();
    }

}
