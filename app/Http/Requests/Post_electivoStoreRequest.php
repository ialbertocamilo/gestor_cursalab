<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Post_electivoStoreRequest extends FormRequest
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
                'nombre' => 'required|min:3',
                'resumen' => 'required',
                'contenido' => 'required',
                'cod_video' => 'required'
            ];
        }
        else{
            $reglas = [
                'nombre' => 'required|min:3',
                'resumen' => 'required',
                'contenido' => 'required',
                'cod_video' => 'required'
            ];
          }
          return $reglas;
    }

    public function messages()
    {
        return [
            'nombre.required' => 'El dato "nombre" es requerido',
            'resumen.required' => 'El dato "resumen" es requerido',
            'contenido.required' => 'El dato "contenido" es requerido',
            'cod_video.required' => 'El dato "codigo de video" es requerido'
            
            
        ];
    }
}
