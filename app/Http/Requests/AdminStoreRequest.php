<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use LangleyFoxall\LaravelNISTPasswordRules\PasswordRules;
use LangleyFoxall\LaravelNISTPasswordRules\Rules\ContextSpecificWords;
use LangleyFoxall\LaravelNISTPasswordRules\Rules\DerivativesOfContextSpecificWords;
use LangleyFoxall\LaravelNISTPasswordRules\Rules\RepetitiveCharacters;
use LangleyFoxall\LaravelNISTPasswordRules\Rules\SequentialCharacters;

use Illuminate\Validation\Rules\Password;

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

        $passwordRules = [
            "{$pass}", 'max:100',  
            Password::min(8)->letters()->numbers()->symbols(),

            "password_available:{$id}",
            // ->mixedCase()->uncompromised(3),

            new ContextSpecificWords($this->email_gestor),
            // new ContextSpecificWords($this->document),
            new ContextSpecificWords($this->name),
            new ContextSpecificWords($this->lastname),
            new ContextSpecificWords($this->surname),
            // new RepetitiveCharacters(),
            // new SequentialCharacters(),
        ];

        $rules = [
            'name' => 'required|min:3|max:255',
            'lastname' => 'required|min:2|max:255',
            'surname' => 'nullable|min:2|max:255',
            // 'document' => 'nullable|max:255',
            // 'password' => "{$pass}|max:255",
            'password' => $passwordRules,

            'email_gestor' => "required|email|max:255|unique:users,email_gestor,{$id},id,deleted_at,NULL",
            'document' => "nullable|min:8|unique:users,document,{$id},id,deleted_at,NULL",

            'username' => 'nullable',
            'active' => 'required',
            'enable_2fa' => 'required',
            // 'phone_number' => 'nullable',
            // 'person_number' => 'nullable',

            'selected_workspaces' => 'required'
        ];

        return $rules;
    }

    public function validationData()
    {
        $this->mergeIfMissing(['active' => INACTIVE]);
        $this->mergeIfMissing(['enable_2fa' => INACTIVE]);

        return $this->all();
    }

    // public function messages()
    // {
    //     return [
    //         'name.required' => 'El dato "nombre" es requerido',
    //         'password.required' => 'El dato "contraseña" es requerido',
    //         'workspacessel.required' => 'Debe seleccionar al menos un rol',
    //         'email_gestor.required' => 'El dato "correo" es requerido',
    //         'email_gestor.email_gestor' => 'El dato "correo " debe tener formato abc@ejemplo.com',
    //         'email_gestor.unique' => 'Este correo ya ha sido regitrado: intente con otro'
    //     ];
    // }
}
