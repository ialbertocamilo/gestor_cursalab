<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
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
        $user = auth()->user();
        $piecesPass = stringConcatEqualNum([$user->document, $user->email], 4);

        return [
            'token' => 'nullable',
            'currpassword' => 'nullable|max:100|min:8',
            'password' => ['required', 'max:100',"not_regex:/($piecesPass)/i", 
                            Password::min(8)->mixedCase()
                                            ->numbers()
                                            ->uncompromised(3) ],
            'repassword' => 'required|max:100|min:8'
        ];
    }

    public function messages()
    {
        return [
            'password.required'=> 'El campo nueva contraseña es obligatorio',
            'password.min' => 'El campo nueva contraseña debe contener al menos 8 caracteres.',
            'password.max' => 'El campo nueva contraseña no debe ser mayor que 100 caracteres.',
            'password.not_regex' => 'El campo nueva contraseña debe ser diferente.',

            'repassword.required' => 'El campo repetir nueva contraseña es obligatorio.',
            'repassword.min' => 'El campo repetir nueva contraseña debe contener al menos 8 caracteres.',
            'repassword.max' => 'El campo repetir nueva contraseña no debe ser mayor que 100 caracteres.',

            'currpassword.required' => 'El campo contraseña actual es obligatorio.',
            'currpassword.min' => 'El campo contraseña actual debe contener al menos 8 caracteres.',
            'currpassword.max' => 'El campo contraseña actual no debe ser mayor que 100 caracteres.'

        ];
    }
}
