<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CargoStoreRequest extends FormRequest
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
        $id = $this->method() == 'PUT' ? $this->segment(2) : 'NULL';

        $reglas = [
            'nombre' => "required|max:150|min:3|unique:cargos,nombre,{$id},id",
        ];

        return $reglas;
    }

    // public function validationData()
    // {
        // $data = [];

        // if ( ! $this->has('estado') )
        //     $data['estado'] = false;

        // return $this->merge($data)->all();
    // }
}
