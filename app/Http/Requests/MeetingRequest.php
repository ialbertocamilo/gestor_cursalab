<?php

namespace App\Http\Requests;

use App\Models\Taxonomy;
use Illuminate\Support\Str;
use Illuminate\Foundation\Http\FormRequest;

class MeetingRequest extends FormRequest
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
            'type_id' => 'bail|required|account_type_available:' . $meeting_id,

            'duration' => 'required|numeric|min:10|max:360',
            'embed' => 'required',

            'attendants' => 'required',

            'description' => 'nullable',
        ];
        $benefit_meeting_type = Taxonomy::getFirstData('meeting', 'type', 'benefits');
        if($benefit_meeting_type?->id == $this->type){
            unset($rules['attendants']);   
        }
        return $rules;
    }

    public function validationData()
    {
        $data['host_id'] = $this->has('host') ? $this->host : null;
        $data['type_id'] = $this->has('type') ? $this->type : null;

        $data['starts_at'] = "{$this->date} {$this->time}:00";
        $data['finishes_at'] = carbonFromFormat($data['starts_at'])->addMinutes($this->duration ?? 0)->format('Y-m-d H:i:s');
        $data['embed'] = false;

        $data['attendants'] = $this->list_attendants;

        return $this->merge($data)->all();
    }

}
