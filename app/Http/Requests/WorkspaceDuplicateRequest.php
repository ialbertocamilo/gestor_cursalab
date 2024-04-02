<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WorkspaceDuplicateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        return [
            'name' => 'required|min:1|max:255',
            'url_powerbi' => 'nullable',
            // 'active' => 'nullable',
            'logo' => 'nullable',
            'logo_negativo' => 'nullable',
            'file_logo' => 'required_without:logo',
            'file_logo_negativo' => 'nullable',
            'duplicate'=>'nullable'
        ];
    }

    // public function validationData(): array
    // {

    //     $data = [];

    //     if ( ! $this->has('active') )
    //         $data['active'] = true;

    //     if ($this->has('marca_agua_estado') ) {
    //         $data['marca_agua_estado'] = ($this->marca_agua_estado == 'true' ||
    //                                       $this->marca_agua_estado == 1 ) ? true : false;
    //     }

    //     return $this->merge($data)->all();
    // }

    public function messages(): array
    {
        return [
            'file_logo.required_without' => 'El campo logo es requerido.',
        ];
    }
}
