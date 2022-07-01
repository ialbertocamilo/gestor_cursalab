<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ErrorStatusRequest extends FormRequest
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
            'status' => 'required',
            'status_id' => 'required',
        ];

        return $reglas;
    }

    public function validationData()
    {
        $data = [];

        $data['status_id'] = $this->status;

        return $this->merge($data)->all();
    }

}
