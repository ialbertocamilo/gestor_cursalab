<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Encuestas_respuestaStoreRequest extends FormRequest
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

        // if ($this->method() == 'PUT'){
        //     $reglas = [
        //         'pregunta_id' => 'required'                
        //     ];
        // }
        // else{
        //     $reglas = [
        //         'pregunta_id' => 'required'
                
        //     ];
        //   }
        //   return $reglas;



        return [
            'pregunta_id' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'pregunta_id.required' => 'El dato "pregunta_id" es requerido'
          
        ];
    }
}
