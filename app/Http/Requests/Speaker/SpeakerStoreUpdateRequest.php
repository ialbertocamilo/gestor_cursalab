<?php

namespace App\Http\Requests\Speaker;

use Illuminate\Foundation\Http\FormRequest;

class SpeakerStoreUpdateRequest extends FormRequest
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
            'name' => 'required',

            'biography' => 'nullable',
            'email' => 'nullable',
            'specialty' => 'nullable',
            'active' => 'nullable',
            'lista_experiencias' => 'nullable',

            'image' => 'nullable',
            'file_image' => 'nullable',
            // 'validateForm' => 'required',
        ];
    }

    public function validationData()
    {
        $active = ($this->active === 'true' or $this->active === true or $this->active === 1 or $this->active === '1');

        $data['active'] = $active;
        // $data['validateForm'] = !!$this->validateForm;

        return $this->merge($data)->all();
    }
}
