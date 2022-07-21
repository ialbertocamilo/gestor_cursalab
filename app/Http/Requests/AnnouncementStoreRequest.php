<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnnouncementStoreRequest extends FormRequest
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
        $reglas = [
            'nombre' => 'required|min:3',
            // 'imagen'=>'required|image|mimes:jpeg,png,jpg|max:5000'
            'contenido' => 'required',
            'imagen' => 'nullable',
            'file_imagen' => 'nullable',

            'archivo' => 'nullable',
            'file_archivo' => 'nullable',

            'estado' => 'nullable',
            'destino' => 'nullable',
            'link' => 'nullable',
            'modules' => 'nullable',
            'publication_starts_at' => 'nullable',
            'publication_ends_at' => 'nullable',
        ];
          // }
        return $reglas;
    }

    public function validationData()
    {
        $data = [];

        if ( ! $this->has('estado') )
            $data['estado'] = false;

        return $this->merge($data)->all();
    }

}
