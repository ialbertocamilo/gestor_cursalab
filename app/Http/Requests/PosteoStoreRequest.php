<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PosteoStoreRequest extends FormRequest
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
                // 'perfiles' => 'required',
                'nombre' => 'required|min:3',
                // 'areas' => 'required',
                // 'resumen' => 'required',
                // 'cod_video' => 'required'
            ];
        }
        else{
            $reglas = [
                // 'perfiles' => 'required',
                // 'areas' => 'required',
                'nombre' => 'required|min:3',
                // 'resumen' => 'required',
                // 'cod_video' => 'required',
                // 'imagen'=>'required|image|mimes:jpeg,png,jpg|max:5000'
                'imagen'=>'required'
            ];
          }
          return $reglas;
    }

    public function messages()
    {
        return [
            // 'perfiles.required' => 'El dato "perfiles" es requerido',
            // 'areas.required' => 'El dato "area" es requerido',
            'nombre.required' => 'El dato "nombre" es requerido',
            // 'resumen.required' => 'El dato "resumen" es requerido',
            // 'cod_video.required' => 'El dato "codigo es requerido',
            'imagen.required' => 'El dato "imagen" es requerido'
            
            
        ];
    }
}
