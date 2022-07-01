<?php

namespace App\Http\Requests\Curso;

use Illuminate\Foundation\Http\FormRequest;

class CursosStoreUpdateRequest extends FormRequest
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
            'config_id' => 'required',
            'categoria_id' => 'required',
            'nombre' => 'required',
            'descripcion' => 'nullable',
            'orden' => 'required',
            'estado' => 'required',
            'requisito_id' => 'nullable',

            'reinicios_programado' => 'nullable',

            'imagen' => 'nullable',
            'plantilla_diploma' => 'nullable',
            'file_imagen' => 'nullable',
            'file_plantilla_diploma' => 'nullable',
        ];
    }
}
