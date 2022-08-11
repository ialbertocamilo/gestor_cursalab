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
        $id = $this->isMethod('post') ? 'NULL' : $this->segment(3);
        $pass = $this->isMethod('post') ? 'required' : 'nullable';

        $rules = [
            'name' => 'required|min:5|max:255',
            'lastname' => 'required|min:5|max:255',
            'surname' => 'required|min:5|max:255',
            'password' => "{$pass}|max:255",

//            'email' => "required|email|max:255|unique:users,email,{$id},id,deleted_at,NULL",
            'email' => "nullable|email|max:255",
            'document' => 'required|min:8',

            'criterion_list' => 'nullable',
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
            'email.required' => 'El dato "correo" es requerido',
            'email.email' => 'El dato "correo " debe tener formato abc@ejemplo.com',
            'email.unique' => 'Este correo ya ha sido regitrado: intente con otro'
        ];
    }
}
