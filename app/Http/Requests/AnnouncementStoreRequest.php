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
        return [
            'nombre' => 'required|min:3|max:120',
            'contenido' => 'required',
            'imagen' => 'nullable',
            'file_imagen' => 'nullable',

            'archivo' => 'nullable',
            'file_archivo' => 'nullable',

            'active' => 'nullable',
            'destino' => 'nullable',
            'link' => 'nullable',
            'module_ids' => 'nullable',
            'publish_date' => 'nullable',
            'end_date' => 'nullable'
        ];
    }

    public function validationData()
    {
        $data = [];

        if ( ! $this->has('active') )
            $data['active'] = false;

        return $this->merge($data)->all();
    }

}
