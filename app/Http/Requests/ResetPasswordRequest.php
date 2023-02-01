<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
        return [
            'token' => 'nullable',
            'currpassword' => 'nullable|max:100|min:8',
            'password' => 'required|max:100|min:8',
            'repassword' => 'required|max:100|min:8'
        ];
    }

    public function messages()
    {
        return [
            'repassword.required' => 'El campo repetir contraseña es obligatorio.',
            'repassword.min' => 'El campo repetir contraseña debe contener al menos 8 caracteres.',
            'repassword.max' => 'El campo repetir contraseña no debe ser mayor que 100 caracteres.',

            'currpassword.required' => 'El campo contraseña actual es obligatorio.',
            'currpassword.min' => 'El campo repetir contraseña debe contener al menos 8 caracteres.',
            'currpassword.max' => 'El campo repetir contraseña no debe ser mayor que 100 caracteres.'

        ];
    }
}
