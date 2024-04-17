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
            'block_stages' => 'boolean',
            'migrate_users' => 'boolean',
            'alert_user_deleted' => 'boolean',
            'message_user_deleted' => 'nullable',
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
        $block_stages = ($this->block_stages === 'true' or $this->block_stages === true or $this->block_stages === 1 or $this->block_stages === '1');
        $migrate_users = ($this->migrate_users === 'true' or $this->migrate_users === true or $this->migrate_users === 1 or $this->migrate_users === '1');
        $alert_user_deleted = ($this->alert_user_deleted === 'true' or $this->alert_user_deleted === true or $this->alert_user_deleted === 1 or $this->alert_user_deleted === '1');
        $config_completed = ($this->config_completed === 'true' or $this->config_completed === true or $this->config_completed === 1 or $this->config_completed === '1');

        $data['active'] = $active;
        $data['limit_absences'] = $limit_absences;
        $data['count_absences'] = $count_absences;
        $data['block_stages'] = $block_stages;
        $data['migrate_users'] = $migrate_users;
        $data['alert_user_deleted'] = $alert_user_deleted;
        $data['config_completed'] = $config_completed;

        $data['instructions'] = $this->instructions;

        return $this->merge($data)->all();
    }
}
