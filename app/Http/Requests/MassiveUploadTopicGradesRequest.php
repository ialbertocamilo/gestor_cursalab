<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MassiveUploadTopicGradesRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'file' => 'required',
            'course' => 'required',
            'topics' => 'nullable',
            'evaluation_type' => 'required',
            'process' => 'required',
            'number_socket' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'file.required' => 'Archivo no encontrado.',
            'topics.required' => 'No se ha seleccionado ningún tema.',
        ];
    }
}
