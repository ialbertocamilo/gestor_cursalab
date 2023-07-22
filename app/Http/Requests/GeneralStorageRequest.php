<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GeneralStorageRequest extends FormRequest
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
            'limit_allowed_storage' => 'nullable',
            'limit_allowed_users' => 'nullable',
            'description' => 'nullable|max:255'
        ];
    }

    public function messages() {
        return [
            'description.max' => 'La descripción debe ser menor a 250 caracteres.'
        ];
    }
}
