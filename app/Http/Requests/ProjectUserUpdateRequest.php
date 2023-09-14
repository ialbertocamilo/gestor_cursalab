<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProjectUserUpdateRequest extends FormRequest
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
            'status_id'=>'required',
            'msg_to_user' => 'max:255',
        ];
    }
    public function messages()
    {

        return [
            'status_id.id.required' => 'El estado es requerido.',
            'msg_to_user.max' => 'El mensaje debe contener máximo 255 caracteres.'
        ];
    }
}