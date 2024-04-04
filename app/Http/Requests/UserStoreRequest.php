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
        // $pass = $this->isMethod('post') ? 'required' : 'nullable';

        // $piecesPass = stringConcatEqualNum([$this->document, $this->email], 4);

        if ($this->isMethod('post')) {
            $passwordRules = [
                "required", 'max:100',
                Password::min(8),
            ];
        } else {
            $passwordRules = [
                "nullable", 'max:100',
                Password::min(8)->letters()->numbers()->symbols(),

                "password_available:{$id}",
                // ->mixedCase()->uncompromised(3),

                new ContextSpecificWords($this->email),
                new ContextSpecificWords($this->document),
                new ContextSpecificWords($this->name),
                new ContextSpecificWords($this->lastname),
                new ContextSpecificWords($this->surname),
                // new RepetitiveCharacters(),
                // new SequentialCharacters(),
            ];
        }

        $rules = [
            'name' => 'required|min:3|max:255',
            'lastname' => 'required|min:2|max:255',
            'surname' => 'required|min:2|max:255',
            // 'password' => "{$pass}|max:255|password_available:{$id}",
            'password' => $passwordRules,
            'document' => "required|min:5|unique:users,document,{$id},id,deleted_at,NULL",
            'username' => 'nullable',
            'phone_number' => 'nullable',
            'person_number' => 'nullable',

            'criterion_list_final' => 'nullable',
            'criterion_list' => 'nullable',
            'national_occupation_id'=>'nullable',
            'curp' => 'nullable'
        ];
        if($id){
            $user = User::where('id',$id)->select('active')->first();
            if($user && $user->active != $this->active){
                $rules['active'] = [new VerifyLimitAllowedUsers($this->method())];
            }
        }
        if(!$id || $id =='NULL'){
            $rules['active'] = $this->active ? [new VerifyLimitAllowedUsers($this->method())] : 'required';
        }
        if($this->email){
            $rules['email'] ="required|email|max:255|unique:users,email,{$id},id,deleted_at,NULL" ;
            // $rules['email'] ="required|email|max:255,email,{$id},id,deleted_at,NULL" ;
        }
        return $rules;
    }

    public function validationData()
    {
        $this->mergeIfMissing(['active' => INACTIVE]);

        return $this->all();
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
