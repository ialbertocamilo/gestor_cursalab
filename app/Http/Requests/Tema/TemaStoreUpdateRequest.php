<?php

namespace App\Http\Requests\Tema;

use Illuminate\Foundation\Http\FormRequest;

class TemaStoreUpdateRequest extends FormRequest
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
            'nombre' => 'required',
            'resumen' => 'nullable',
            'contenido' => 'nullable',
            'estado' => 'required',
            'orden' => 'required',
            'categoria_id' => 'required',
            'curso_id' => 'required',

//            'media' => 'nullable',

            'new_medias' => 'nullable',
            'medias' => 'nullable',

            'imagen' => 'nullable',
            'file_imagen' => 'nullable',

            'evaluable' => 'required',
            'tipo_ev' => 'nullable',
            'requisito_id' => 'nullable',
            'tags' => 'nullable',
            'check_tipo_ev' => 'nullable'

        ];
    }

    public function validationData()
    {
        $estado = ($this->estado==='true' OR $this->estado === true OR $this->estado === 1 OR $this->estado === '1' );

        $data['estado'] = $estado;

        return $this->merge($data)->all();
    }
}
