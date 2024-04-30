<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChecklistStoreRequest extends FormRequest
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
            'title' => 'required',
            'type_id'=> 'required',
            'image'=> 'nullable',
            'modality_id'=> 'required',
            'extra_attributes'=>'required',
            'course_id' => 'nullable',
            'description'=>'nullable',
            'finishes_at'=>'nullable',
            'image'=>'nullable',
            'evaluation_types'=>'nullable',
        ];
    }
    public function messages()
    {
        return [
            'title.required' => 'El dato título es requerido',
            'type_id.required' => 'El tipo de checklist es requerido',
            'modality_id.required' => 'La modalidad es requerida',
        ];
    }


    public function validationData(){
        $extra_attributes = json_decode($this->extra_attributes, true) ?? [];

        $extra_attributes['qualification_type'] = $extra_attributes['qualification_type'];
        $extra_attributes['autocalificate_entity_criteria'] = $extra_attributes['autocalificate_entity_criteria'] ?? null;
        $extra_attributes['autocalificate_entity_criteria_value'] = $extra_attributes['autocalificate_entity_criteria_value'] ?? null;

        $extra_attributes['visualiazation_results'] = verifyBooleanValue($extra_attributes['visualiazation_results'] ?? null);
        $extra_attributes['view_360'] = verifyBooleanValue($extra_attributes['view_360'] ?? null);
        $extra_attributes['comments_if_checklist_completed'] = verifyBooleanValue($extra_attributes['comments_if_checklist_completed'] ?? null);
        $extra_attributes['replicate'] = verifyBooleanValue($extra_attributes['replicate'] ?? null);
        $extra_attributes['required_signature_supervisor'] = verifyBooleanValue($extra_attributes['required_signature_supervisor'] ?? null);
        $extra_attributes['required_signature_supervised'] = verifyBooleanValue($extra_attributes['required_signature_supervised'] ?? null);
        $extra_attributes['required_geolocation'] = verifyBooleanValue($extra_attributes['required_geolocation'] ?? null);
        $extra_attributes['autocalificate_entity'] = verifyBooleanValue($extra_attributes['autocalificate_entity'] ?? null);
        $extra_attributes['required_comments'] = verifyBooleanValue($extra_attributes['required_comments'] ?? null);
        $extra_attributes['required_action_plan'] = verifyBooleanValue($extra_attributes['required_action_plan'] ?? null);
        
        $this->merge(['extra_attributes' => $extra_attributes]);
        return $this->all();
    }
}
