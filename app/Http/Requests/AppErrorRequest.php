<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AppErrorRequest extends FormRequest
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
            'message' => 'required',
            'method' => 'nullable',
            'status' => 'nullable',
            'result' => 'nullable',
            'code' => 'nullable',
            'evento_id' => 'nullable'
        ];

        return $reglas;
    }
}
