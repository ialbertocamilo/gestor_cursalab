<?php

namespace App\Http\Requests\Escuela;

use Illuminate\Foundation\Http\FormRequest;

class EscuelaStoreUpdateRequest extends FormRequest
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
            // 'config_id' => 'required',
            'name' => 'required|max:120',
            'modalidad' => 'nullable',
            // 'nombre_ciclo_0' => 'nullable',
            'position' => 'nullable',
            'active' => 'required',

            'scheduled_restarts' => 'nullable',

            'imagen' => 'nullable',
            'file_imagen' => 'nullable',
            'plantilla_diploma' => 'nullable',
            'file_plantilla_diploma' => 'nullable',
        ];
    }
}
