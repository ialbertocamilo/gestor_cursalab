<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MallasStoreRequest extends FormRequest
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

        if ($this->method() == 'PUT'){
            $reglas = [
                // 'archivo' => 'required|max:5000',
            ];
        }
        else{
            $reglas = [
                // 'archivo' => 'required|max:5000'
                'archivo' => 'required'
            ];
          }
          return $reglas;
    }

    public function messages()
    {
        return [
            'archivo.required' => 'El dato "archivo" es requerido.'
            
        ];
    }
}
