<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StateUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'users_document' => 'required_without:users_email|array',
            'users_email' => 'required_without:users_document|array',
        ];
    }

    public function messages(){
        return [
            'users_document.required_without' => 'Es necesario el campo users_document.',
            'users_email.required_without' => 'Es necesario el campo users_email.',
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(['errors' => $validator->errors()], 422));
    }
}
