<?php

namespace App\Http\Requests\Induction;

use Illuminate\Foundation\Http\FormRequest;

class ProcessStoreUpdateRequest extends FormRequest
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
            'title' => 'required',
            'description' => 'nullable',
            'limit_absences' => 'boolean',
            'count_absences' => 'boolean',
            'absences' => 'nullable|numeric',
            'active' => 'nullable',
        ];
    }

    public function validationData()
    {
        $active = ($this->active === 'true' or $this->active === true or $this->active === 1 or $this->active === '1');
        $limit_absences = ($this->limit_absences === 'true' or $this->limit_absences === true or $this->limit_absences === 1 or $this->limit_absences === '1');
        $count_absences = ($this->count_absences === 'true' or $this->count_absences === true or $this->count_absences === 1 or $this->count_absences === '1');

        $data['active'] = $active;
        $data['limit_absences'] = $limit_absences;
        $data['count_absences'] = $count_absences;

        return $this->merge($data)->all();
    }
}
