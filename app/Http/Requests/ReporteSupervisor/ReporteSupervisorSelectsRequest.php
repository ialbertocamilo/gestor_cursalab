<?php

namespace App\Http\Requests\ReporteSupervisor;

use Illuminate\Foundation\Http\FormRequest;

class ReporteSupervisorSelectsRequest extends FormRequest
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
            'escuela_id' => 'required'
        ];
    }
}
