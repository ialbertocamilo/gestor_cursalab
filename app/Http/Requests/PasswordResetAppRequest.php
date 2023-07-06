<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Rules\CustomContextSpecificWords;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use LangleyFoxall\LaravelNISTPasswordRules\PasswordRules;
use LangleyFoxall\LaravelNISTPasswordRules\Rules\ContextSpecificWords;
use LangleyFoxall\LaravelNISTPasswordRules\Rules\DerivativesOfContextSpecificWords;
use LangleyFoxall\LaravelNISTPasswordRules\Rules\RepetitiveCharacters;
use LangleyFoxall\LaravelNISTPasswordRules\Rules\SequentialCharacters;
use Laravel\Sanctum\PersonalAccessToken;

class PasswordResetAppRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        // $document = NULL;

        // if($this->email) {
        //     $user = new User;
        //     $current = $user->currentUserByEnviroment('GESTOR', $this->email); 
        //     $document = $current->document ?? NULL;
        // }else {
        //     $document = $this->document;
        // }

        // $piecesPass = stringConcatEqualNum([$document, $this->email], 4);

        $field = $this->email ? 'email' : 'document';
        $value = $this->email ? $this->email : $this->document;

        // if($this->email) {
        // $user = new User;
        $user = User::where($field, $value)->first();
        $user_id = $user->id ?? NULL;
        
        return [
            'email' => 'nullable|email',
            'document' => 'nullable',
            'password' => ['required', 'confirmed',
                           'max:100',
                           "password_available:{$user_id}",
                           // "not_regex:/($piecesPass)/i", 
                            Password::min(8)->letters()->numbers()->symbols(),

                            // new CustomContextSpecificWords(
                            //     $user->email ?? NULL, 
                            //     'El campo correo electrónico no es un correo válido'
                            // ),
                            // new CustomContextSpecificWords(
                            //     $user->document ?? NULL, 
                            //     'No puedes incluir tu Documento de Identidad \''.$user->document.'\' en la contraseña.'."\n Usa otros términos que te sean fácil de recordar."
                            // ),
                            // new CustomContextSpecificWords(
                            //     $user->name ?? NULL, 
                            //     'No puedes incluir tu nombre \''.$user->name.'\' en la contraseña.'."\n Usa otros términos que te sean fácil de recordar."
                            // ),
                            // new CustomContextSpecificWords($user->lastname ?? NULL, 
                            //     'No puedes incluir tu nombre ni apellido  \''.$user->lastname.'\' en la contraseña.'."\n Usa otros términos que te sean fácil de recordar."
                            // ),
                            // new CustomContextSpecificWords($user->surname ?? NULL,
                            //     'No puedes incluir tu nombre ni apellido  \''.$user->surname.'\' en la contraseña.'."\n Usa otros términos que te sean fácil de recordar."
                            // ),

                            new ContextSpecificWords($user->email ?? NULL),
                            new ContextSpecificWords($user->document ?? NULL),
                            new ContextSpecificWords($user->name ?? NULL),
                            new ContextSpecificWords($user->lastname ?? NULL),
                            new ContextSpecificWords($user->surname ?? NULL),
                                            // ->uncompromised(3)
                        ],
            'token' => 'required',
            'os' => 'nullable',
            'version' => 'nullable'
        ];
    }

    // public function messages()
    // {
    //      return [   
    //                 'password.required'=> 'El campo nueva contraseña es obligatorio',
    //                 'password.min' => 'El campo nueva contraseña debe contener al menos 8 caracteres.',
    //                 'password.max' => 'El campo nueva contraseña no debe ser mayor que 100 caracteres.',
    //                 'password.not_regex' => 'El campo nueva contraseña debe ser diferente.'
    //             ];
    // }
}
