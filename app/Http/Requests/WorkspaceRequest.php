<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WorkspaceRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        return [
            'name' => 'required|min:5|max:255',
            'url_powerbi' => 'nullable',
            'active' => 'nullable',
            'logo' => 'nullable',
            'logo_negativo' => 'nullable',
            'file_logo' => 'required_without:logo',
            'file_logo_negativo' => 'nullable',
            'selected_criteria' => 'nullable',
            'limit_allowed_users_type' => 'nullable',
            'limit_allowed_users_limit' => 'nullable',
            'selected_functionality' => 'nullable',
        ];
    }

    public function validationData(): array
    {

        $data = [];

        if ( ! $this->has('active') )
            $data['active'] = true;

        return $this->merge($data)->all();
    }

    public function messages(): array
    {
        return [
            'file_logo.required_without' => 'El campo logo es requerido.',
        ];
    }
}
