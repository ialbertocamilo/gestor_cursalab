<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Models\Guest;
use Illuminate\Foundation\Http\FormRequest;

class GuestInvitationRequest extends FormRequest
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
            'email' => [
                'required',
                'email'
            ],
        ];
    }
    public function messages()
    {
        return [
            'email.email' => 'El campo :attribute debe ser una dirección de correo electrónico válida.',
            'email.required' => 'El campo :attribute es obligatorio.',
        ];
    }
    public function withValidator($factory) {
        $verify_user = User::where('email', $this->email)->get();
        $verify_guest = Guest::where('email', $this->email)->get();

        $factory->after(function ($factory) use ($verify_guest,$verify_user) {
            if (!$verify_user->isEmpty()) {
                $factory->errors()->add('email', 'Este email ya se encuentra registrado.');
            }
            if (!$verify_guest->isEmpty()) {
                $factory->errors()->add('email', 'Este email ya ha sido invitado.');
            }
        });
        return $factory;
}
}
