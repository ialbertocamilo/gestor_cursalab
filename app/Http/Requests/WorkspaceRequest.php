<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WorkspaceRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        return [
            'name' => 'required|min:1|max:255',
            'url_powerbi' => 'nullable',
            'active' => 'nullable',
            'logo' => 'nullable',
            'logo_negativo' => 'nullable',
            'file_logo' => 'required_without:logo',
            'file_logo_negativo' => 'nullable',
            'selected_criteria' => 'nullable',
            'limit_allowed_storage' => 'nullable',
            'limit_allowed_users_type' => 'nullable',
            'limit_allowed_users_limit' => 'nullable',
            'limit_allowed_media_convert' => 'nullable',
            'limit_allowed_ia_evaluations' => 'nullable',
            'limit_descriptions_jarvis' => 'nullable',
            'context_jarvis' => 'nullable',
            'openia_token'=> 'nullable',
            'openia_model'=> 'nullable',

            'show_logo_in_app' => 'nullable',

            'logo_marca_agua' => 'nullable',
            'marca_agua_estado' => 'nullable',
            'notificaciones_push_envio_inicio' => 'nullable',
            'notificaciones_push_envio_intervalo' => 'nullable',
            'notificaciones_push_chunk' => 'nullable',
            'selected_functionality' => 'nullable',

            'qualification_type_id' => 'required',
            'criterio_id_fecha_inicio_reconocimiento' => 'nullable',
            'share_diplomas_social_media' => 'nullable',
            'criteria' => 'required',

            'dc3_configuration'=>'nullable',
            'reminders_configuration'=>'nullable'
        ];
    }

    public function validationData(): array
    {
        $data = [];

        if ( ! $this->has('active') )
            $data['active'] = true;

        if ($this->has('marca_agua_estado') ) {
            $data['marca_agua_estado'] = ($this->marca_agua_estado == 'true' ||
                                          $this->marca_agua_estado == 1 ) ? true : false;
        }

        if ($this->has('show_logo_in_app') ) {
            $data['show_logo_in_app'] = ($this->show_logo_in_app == 'true' ||
                                          $this->show_logo_in_app == 1 ) ? true : false;
        }

        if ($this->has('share_diplomas_social_media') ) {
            $data['share_diplomas_social_media'] = ($this->share_diplomas_social_media == 'true' ||
                $this->share_diplomas_social_media == 1 ) ? true : false;
        }


        $data['qualification_type_id'] = $this->has('qualification_type') ? $this->qualification_type : null;

        $criteria_workspace = $this->criteria_workspace ? json_decode($this->criteria_workspace, true) : [];

        $data['criteria'] = [];

        foreach ($criteria_workspace as $row) {

            if ($row['available']) {

                $fields = [];

                foreach ($row['fields'] as $field) {

                    if ($field['type'] == 'boolean') {
                        $fields[$field['code']] = $field['available'] ? 1 : NULL;
                    }

                    if ($field['type'] == 'text') {
                        $fields[$field['code']] = $field['text'] ?? NULL;
                    }
                }

                $data['criteria'][$row['criterion_id']] = $fields;
            }
        }

        return $this->merge($data)->all();
    }

    public function messages(): array
    {
        return [
            'file_logo.required_without' => 'El campo logo es requerido.',
        ];
    }
}
