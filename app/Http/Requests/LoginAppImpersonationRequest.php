<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginAppImpersonationRequest extends FormRequest
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
        $fields = config('app.impersonation.fields');

        $data = [];

        foreach ($fields as $field) {
            $data[$field['name']] = $field['required'] ? 'required' : 'nullable';
        }

        return $data;
    }
}
