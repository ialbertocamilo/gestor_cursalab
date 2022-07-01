<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UsuarioStoreRequest extends FormRequest
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
                'nombre' => 'required',
                // 'cargo' => 'required',
                // 'dni' => 'required|numeric|digits:8',
                'dni' => 'required|numeric',
                // 'email' => 'required|email'

            ];
        }
        else{
            $reglas = [
                'nombre' => 'required',
                // 'cargo' => 'required',
                'dni' => 'required|numeric|unique:usuarios',
                // 'dni' => 'required|numeric|digits:8|unique:usuarios',
                // 'email' => 'required|email|unique:usuarios',
                'password' => 'required'
            ];
          }
          return $reglas;
    }

    public function messages()
    {
        return [
            'nombre.required' => 'El dato "nombres y apellidos" es requerido',
            'cargo.required' => 'El dato "cargo" es requerido',
            'dni.required' => 'El dato "dni" es requerido',
            'dni.numeric' => 'El dato "dni" debe ser numérico',
            'dni.digits' => 'El dato "dni" debe tener contener 8 digitos',
            'dni.unique' => 'El dato "dni" ya ha sido regitrado, intenta con otro',
            'email.required' => 'El dato "correo" es requerido',
            'email.email' => 'El dato "correo " debe tener formato abc@ejemplo.com',
            'email.unique' => 'Este correo ya ha sido regitrado, intenta con otro',
            'password.required' => 'El dato "contraseña" es requerido'            
        ];
    }
}
