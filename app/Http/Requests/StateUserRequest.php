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

    private $identificators = ['document','username','email']; // The values ​​must be user attributes.
    public function rules()
    {
        return [
            'identificator' => ['required',Rule::in($this->identificators)],
            'users' => 'required:users|array',
        ];
    }

    public function messages(){
        return [
            'identificator.required' => 'The field identificator is required.',
            'identificator.in' => 'the identifier field accept only these values: '.implode(',',$this->identificators).' .',
            'users.required' => 'The field users is required.',
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(['errors' => $validator->errors()], 422));
    }
}
