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
            "required", 'confirmed', 'max:100',
            Password::min(8)->letters()->numbers()->symbols(),
            "password_available:{$user->id}",
            // ->mixedCase()->uncompromised(3),

            new CustomContextSpecificWords($user->email ?? NULL, 'email'),
            new CustomContextSpecificWords($user->email_gestor ?? NULL, 'email_gestor'),
            new CustomContextSpecificWords($user->document ?? NULL, 'document'),
            new CustomContextSpecificWords($user->name ?? NULL, 'name'),
            new CustomContextSpecificWords($user->lastname ?? NULL, 'lastname'),
            new CustomContextSpecificWords($user->surname ?? NULL, 'surname'),
            // new RepetitiveCharacters(),
            // new SequentialCharacters(),
        ];

        $rules = [
            'oldPassword' => 'nullable|max:100|min:8|current_password',
            'newPassword' => $passwordRules,
            'newPasswordConfirmation' => 'required|max:100|min:8'
        ];

        return $rules;
    }

    protected function validationErrorMessages() 
    {
        return [
                'password.password_available' => 'Has usado esta contraseña previamente, intenta con una nueva.',
                // 'email.email' => 'El campo correo electrónico no es un correo válido'
            ];
    }

}
