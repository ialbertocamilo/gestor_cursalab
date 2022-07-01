<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VademecumStoreRequest extends FormRequest
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
        $id = $this->segment(2);

        return [
            'nombre' => "required|max:255|unique:vademecum,nombre,{$id},id,deleted_at,NULL",
            'categoria_id' => 'nullable',
            'subcategoria_id' => 'nullable',

            'media' => 'nullable',
            'file_media' => 'nullable',
            // 'media_id' => 'required|exists:media,id,deleted_at,NULL',

            'modulos' => 'nullable',

            'estado' => 'required',
        ];
    }

    public function validationData()
    {
        $data = [];

        if ( ! $this->has('estado') )
            $data['estado'] = false;

        $data['categoria_id'] = $this->has('categoria') ? $this->categoria : null;

        $data['subcategoria_id'] = $this->has('subcategoria') ? $this->subcategoria : null;

        return $this->merge($data)->all();
    }

    // public function messages()
    // {
    //     return [
    //         'palabra.required' => 'El dato "palabra" es requerido',
    //         'descripcion.required' => 'El dato "descripción" es requerido'
    //     ];
    // }
}
