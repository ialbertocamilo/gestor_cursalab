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
            'token' => 'required',
            'password' => 'required|max:20|min:4',
            'repassword' => 'required|max:20|min:4'
        ];
    }

    public function messages()
    {
        return [
            'repassword.required' => 'El campo repetir contraseña es obligatorio.',
            'repassword.min' => 'El campo repetir contraseña de contener al menos 4 caracteres.',
            'repassword.max' => 'El campo repetir contraseña no debe ser mayor que 20 caracteres.'
        ];
    }
}
