<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PermisoStoreRequest extends FormRequest
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
        return [
            'name' => 'required|min:3',
            'slug' => ['required','unique:permissions,slug']
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'El dato "name" es requerido',
            'slug.required'  => 'El dato "slug" es requerido{$this->slug}',
            'slug.unique'  => 'El dato del campo "slug" ya existe',
        ];
    }
}
