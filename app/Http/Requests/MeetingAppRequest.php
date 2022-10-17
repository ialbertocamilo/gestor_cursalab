<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

use App\Models\Taxonomy;

class MeetingAppRequest extends FormRequest
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
        $meeting_id = $this->method() == 'PUT' ? $this->segment(4) : 'NULL';

        $rules = [
            'name' => 'required|min:5|max:255',

            'starts_at' => 'required|date_format:Y-m-d H:i:s|meeting_date_after_or_equal_today:' . $meeting_id,
            'finishes_at' => 'required|date_format:Y-m-d H:i:s|after:starts_at',

            'host_id' => 'bail|required|available_for_meeting:' . $meeting_id,
            'type_id' => 'bail|required|account_type_available:' . $meeting_id,

            'duration' => 'required|numeric|min:10|max:360',
            'embed' => 'required',

            'attendants' => 'required',
            // 'attendants.*.usuario_id' => 'required',

            'description' => 'nullable',
        ];

        return $rules;
    }

    public function validationData()
    {
        $type = Taxonomy::getFirstData('meeting', 'type', 'room');

        $data['host_id'] = auth()->user()->id;


        $data['type_id'] = $type->id;

        $data['starts_at'] = "{$this->date} {$this->time}:00";
        $data['finishes_at'] = carbonFromFormat($data['starts_at'])->addMinutes($this->duration ?? 0)->format('Y-m-d H:i:s');
        $data['embed'] = false;

        // $data['attendants'] = $this->list_attendants;
        // $data['attendants'] = $this->list_attendants;

        return $this->merge($data)->all();
    }

}
