<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserStoreRequest extends FormRequest
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

        // Para EDITAR
        if ($this->method() == 'PUT'){
            $reglas = [
                'name' => 'required',
                'email' => 'required|email|unique:users'
                
            ];
        }
        else{
            //pARA CREAR
            $reglas = [
                'name' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required'
            ];
          }
          return $reglas;
    }

    public function messages()
    {
        return [
            'name.required' => 'El dato "nombre" es requerido',
            'password.required' => 'El dato "contraseña" es requerido',
            'email.required' => 'El dato "correo" es requerido',
            'email.email' => 'El dato "correo " debe tener formato abc@ejemplo.com',
            'email.unique' => 'Este correo ya ha sido regitrado: intente con otro'
            
            
        ];
    }
}
