<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Validation\Rule;
use App\Rules\VerifyLimitAllowedUsers;
use Illuminate\Validation\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;

use LangleyFoxall\LaravelNISTPasswordRules\PasswordRules;
use LangleyFoxall\LaravelNISTPasswordRules\Rules\ContextSpecificWords;
use LangleyFoxall\LaravelNISTPasswordRules\Rules\DerivativesOfContextSpecificWords;
use LangleyFoxall\LaravelNISTPasswordRules\Rules\RepetitiveCharacters;
use LangleyFoxall\LaravelNISTPasswordRules\Rules\SequentialCharacters;
use App\Rules\CustomContextSpecificWords;

class UserAppPasswordUpdateRequest extends FormRequest
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
        $user = auth()->user();

        $passwordRules = [
            // "nullable", 'max:100',
            "required", 'confirmed', 'max:100',
            Password::min(8)->letters()->numbers()->symbols(),

            "password_available:{$user->id}",
            // ->mixedCase()->uncompromised(3),

            new CustomContextSpecificWords($user->email ?? NULL, 'email'),
            new CustomContextSpecificWords($user->document ?? NULL, 'document'),
            new CustomContextSpecificWords($user->name ?? NULL, 'name'),
            new CustomContextSpecificWords($user->lastname ?? NULL, 'lastname'),
            new CustomContextSpecificWords($user->surname ?? NULL, 'surname'),
            // new RepetitiveCharacters(),
            // new SequentialCharacters(),
        ];

        $rules = [
            // 'password' => "{$pass}|max:255|password_available:{$id}",
            'password' => $passwordRules,
            'document' => "required|min:8|unique:users,document,{$id},id,deleted_at,NULL",
            // 'username' => 'nullable',
        ];

        // if($this->email){
        //     // $rules['email'] ="required|email|max:255|unique:users,email,{$id},id,deleted_at,NULL" ;
        //     $rules['email'] ="required|email|max:255,email,{$id},id,deleted_at,NULL" ;
        // }
        return $rules;
    }

    protected function validationErrorMessages() 
    {
        return [
                'password.password_available' => 'Has usado esta contraseña previamente, intenta con una nueva.',
                'email.email' => 'El campo correo electrónico no es un correo válido'
            ];
    }

    // public function messages()
    // {
    //     return [
    //         'name.required' => 'El campo nombre es requerido',

    //         'password.required' => 'El campo contraseña es requerido',
    //         'password.min' => 'El campo contraseña debe contener al menos 8 caracteres',
    //         'password.max' => 'El campo contraseña no debe ser mayor a 100 caracteres',
    //         'password.not_regex' => 'El campo contraseña no debe ser similar a tu nro de documento y/o email',

    //         'document.unique' => 'Este documento ya ha sido registrado: intente con otro',

    //         'email.required' => 'El campo correo es requerido',
    //         'email.email' => 'El campo correo debe tener formato abc@ejemplo.com',
    //         'email.unique' => 'Este correo ya ha sido regitrado: intente con otro'
    //     ];
    // }
}
