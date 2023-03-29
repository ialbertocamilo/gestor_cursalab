<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use App\Models\User;

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
        $document = NULL;

        if($this->email) {
            $user = new User;
            $current = $user->currentUserByEnviroment('GESTOR', $this->email); 
            $document = $current->document ?? NULL;
        }else {
            $document = $this->document;
        }

        $piecesPass = stringConcatEqualNum([$document, $this->email], 4);
        
        return [
            'email' => 'nullable|email',
            'document' => 'nullable',
            'password' => ['required', 'confirmed',
                           'max:100',"not_regex:/($piecesPass)/i", 
                            Password::min(8)->mixedCase()
                                            ->numbers()
                                            ->uncompromised(3) ],
            'token' => 'required',
            'os' => 'nullable',
            'version' => 'nullable'
        ];
    }
    public function messages()
    {
         return [   
                    'password.required'=> 'El campo nueva contraseña es obligatorio',
                    'password.min' => 'El campo nueva contraseña debe contener al menos 8 caracteres.',
                    'password.max' => 'El campo nueva contraseña no debe ser mayor que 100 caracteres.',
                    'password.not_regex' => 'El campo nueva contraseña debe ser diferente.'
                ];
    }
}
