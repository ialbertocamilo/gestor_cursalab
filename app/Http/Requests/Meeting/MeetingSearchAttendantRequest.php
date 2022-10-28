<?php

namespace App\Http\Requests\Meeting;

use Illuminate\Foundation\Http\FormRequest;

class MeetingSearchAttendantRequest extends FormRequest
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
            'starts_at' => 'required',
            'finishes_at' => 'required',
            'duration' => 'required',
            'q' => 'nullable|min:3',

            'meeting_id' => 'nullable',
            'config_id' => 'nullable',
            'file' => 'nullable',
            'grupos_id' => 'nullable',
            'exclude_host_id' => 'nullable'
        ];
    }

    public function validationData()
    {
        $data['starts_at'] = "{$this->date} {$this->time}:00";
        $data['finishes_at'] = carbonFromFormat($data['starts_at'])
                                ->addMinutes($this->duration ?? 0)
                                ->format('Y-m-d H:i:s');

        return $this->merge($data)->all();
    }
}
