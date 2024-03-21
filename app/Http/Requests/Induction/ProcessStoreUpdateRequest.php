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
            'subworkspaces' => 'required',
            'description' => 'nullable',
            'limit_absences' => 'boolean',
            'count_absences' => 'boolean',
            'absences' => 'nullable|numeric',
            'active' => 'nullable',
            'starts_at' => 'nullable',
            'finishes_at' => 'nullable',
            'instructions' => 'nullable',

            'background_mobile' => 'nullable',
            'file_background_mobile' => 'nullable',
            'background_web' => 'nullable',
            'file_background_web' => 'nullable',
            'logo' => 'nullable',
            'file_logo' => 'nullable',
            'image_guia' => 'nullable',
            'image_guide_name' => 'nullable',
            'file_image_guia' => 'nullable',
            'icon_finished_name' => 'nullable',
            'icon_finished' => 'nullable',
            'file_icon_finished' => 'nullable',

            'color' => 'nullable',
            'color_map_even' => 'nullable',
            'color_map_odd' => 'nullable',

            'config_completed' => 'nullable'
        ];
    }

    public function validationData()
    {
        $active = ($this->active === 'true' or $this->active === true or $this->active === 1 or $this->active === '1');
        $limit_absences = ($this->limit_absences === 'true' or $this->limit_absences === true or $this->limit_absences === 1 or $this->limit_absences === '1');
        $count_absences = ($this->count_absences === 'true' or $this->count_absences === true or $this->count_absences === 1 or $this->count_absences === '1');
        $config_completed = ($this->config_completed === 'true' or $this->config_completed === true or $this->config_completed === 1 or $this->config_completed === '1');

        $data['active'] = $active;
        $data['limit_absences'] = $limit_absences;
        $data['count_absences'] = $count_absences;
        $data['config_completed'] = $config_completed;

        $data['instructions'] = $this->instructions;

        return $this->merge($data)->all();
    }
}
