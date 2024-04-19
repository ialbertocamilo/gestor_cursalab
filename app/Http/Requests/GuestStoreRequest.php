<?php

namespace App\Http\Requests;

use App\Rules\CustomContextSpecificWords;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rules\Password AS RulePassword;

class GuestStoreRequest extends FormRequest
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
        $passwordRules = [
            "required", 'confirmed', 'max:100','min:5',
            // RulePassword::min(8)->letters()->numbers()->symbols(),
            // new CustomContextSpecificWords($this->email ?? NULL, 'email'),
            // new CustomContextSpecificWords($this->document ?? NULL, 'document'),
            // new CustomContextSpecificWords($this->name ?? NULL, 'name'),
            // new CustomContextSpecificWords($this->lastname ?? NULL, 'lastname'),
            // new CustomContextSpecificWords($this->surname ?? NULL, 'surname'),
        ];
        return [
            'name'=>'required',
            'lastname'=>'required',
            'surname'=>'required',
            'email'=>'required|email|max:255|unique:users,email,{$id},id,deleted_at,NULL',
            'document' => 'required|min:8|unique:users,document,{NULL},id,deleted_at,NULL',
            'password' => $passwordRules,
            'criterion_list' => 'nullable',
            'criterion_list_final' => 'nullable',
        ];
    }
    public function messages()
    {
        return [
            'password.password_available' => 'Has usado esta contraseña previamente, intenta con una nueva.',
            'email.email' => 'El campo correo electrónico no es un correo válido'
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'error' => [
                'message' => 'La solicitud contiene errores de validación',
                'errors' => $validator->errors(),
            ]
        ], 422));
    }
}
