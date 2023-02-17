<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminStoreRequest extends FormRequest
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
        $id = $this->isMethod('post') ? 'NULL' : $this->segment(2);
        $pass = $this->isMethod('post') ? 'required' : 'nullable';

        $rules = [
            'name' => 'required|min:3|max:255',
            'lastname' => 'required|min:2|max:255',
            'surname' => 'required|min:2|max:255',
            'password' => "{$pass}|max:255",

            'email_gestor' => "required|email|max:255|unique:users,email_gestor,{$id},id,deleted_at,NULL",
            // 'document' => "required|min:8|unique:users,document,{$id},id,deleted_at,NULL",

            'username' => 'nullable',
            'phone_number' => 'nullable',
            'person_number' => 'nullable',

            'workspacessel' => 'required'
        ];

        return $rules;
    }

    public function validationData()
    {
        $this->mergeIfMissing(['active' => INACTIVE]);

        return $this->all();
    }

    public function messages()
    {
        return [
            'name.required' => 'El dato "nombre" es requerido',
            'password.required' => 'El dato "contraseña" es requerido',
            'workspacessel.required' => 'Debe seleccionar al menos un rol',
            'email_gestor.required' => 'El dato "correo" es requerido',
            'email_gestor.email_gestor' => 'El dato "correo " debe tener formato abc@ejemplo.com',
            'email_gestor.unique' => 'Este correo ya ha sido regitrado: intente con otro'
        ];
    }
}
