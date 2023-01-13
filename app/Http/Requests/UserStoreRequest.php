<?php

namespace App\Http\Requests;

use App\Rules\VerifyLimitAllowedUsers;
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
        $id = $this->isMethod('post') ? 'NULL' : $this->segment(2);
        $pass = $this->isMethod('post') ? 'required' : 'nullable';
        $rules = [
            'name' => 'required|min:3|max:255',
            'lastname' => 'required|min:2|max:255',
            'surname' => 'required|min:2|max:255',
            'password' => "{$pass}|max:255",

            'document' => "required|min:8|unique:users,document,{$id},id,deleted_at,NULL",

            'username' => 'nullable',
            'phone_number' => 'nullable',
            'person_number' => 'nullable',

            'criterion_list_final' => 'nullable',
            'criterion_list' => 'nullable',

            'active' => [new VerifyLimitAllowedUsers($this->method())],
        ];
        if($this->email){
            $rules['email'] ="required|email|max:255|unique:users,email,{$id},id,deleted_at,NULL" ;
        }
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
