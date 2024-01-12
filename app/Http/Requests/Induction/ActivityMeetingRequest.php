<?php

namespace App\Http\Requests\Induction;

use App\Models\Taxonomy;
use Illuminate\Support\Str;
use Illuminate\Foundation\Http\FormRequest;

class ActivityMeetingRequest extends FormRequest
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
        $meeting_id = ($this->method() == 'PUT')
                        ? $this->segment(2)
                        : 'NULL';
        $rules = [
            'name' => 'required|min:5|max:255',

            'starts_at' => 'required|date_format:Y-m-d H:i:s|meeting_date_after_or_equal_today:' . $meeting_id,
            'finishes_at' => 'required|date_format:Y-m-d H:i:s|after:starts_at',

            'host_id' => 'bail|required|available_for_meeting:' . $meeting_id,

            'duration' => 'required|numeric|min:10|max:360',
            'embed' => 'required',

            'description' => 'nullable',
            'model_id' => 'nullable'
        ];
        return $rules;
    }

    public function validationData()
    {
        $data['host_id'] = $this->has('host') ? $this->host : null;
        $data['model_id'] = $this->model_id;

        $data['starts_at'] = "{$this->date} {$this->time}:00";
        $data['finishes_at'] = carbonFromFormat($data['starts_at'])->addMinutes($this->duration ?? 0)->format('Y-m-d H:i:s');
        $data['embed'] = false;

        return $this->merge($data)->all();
    }

}
