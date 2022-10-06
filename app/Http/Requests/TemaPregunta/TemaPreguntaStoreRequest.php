<?php

namespace App\Http\Requests\TemaPregunta;

use Illuminate\Foundation\Http\FormRequest;

class TemaPreguntaStoreRequest extends FormRequest
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
            'id' => 'nullable',
            'pregunta' => 'required',
            'nuevasRptas' => 'required',
            'rpta_ok' => 'required',
            'score' => 'nullable',
            'required' => 'nullable',
            'active' => 'required',
        ];
    }

    public function validationData(): array
    {
        $data = [];
        $evaluationType = $this->get('evaluation_type');

        if ($evaluationType === 'open') {
            $data['rpta_ok'] = 0;
            $data['rptas_json'] = '{}';
        }

        if ( ! $this->has('required') )
            $data['required'] = false;


        return $this->merge($data)->all();
    }
}
