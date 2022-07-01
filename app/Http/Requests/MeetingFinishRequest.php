<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MeetingFinishRequest extends FormRequest
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
        // $meeting_id = $this->method() == 'PUT' ? $this->segment(2) : 'NULL';

        $rules = [
            'meeting_id' => 'required|meeting_can_be_finished',
            // 'host_id' => 'bail|required|available_for_meeting:' . $meeting_id,
        ];

        return $rules;
    }

    public function validationData()
    {
        $data['meeting_id'] = $this->method() == 'PUT' ? $this->segment(2) : 'NULL';

        return $this->merge($data)->all();
    }

}
