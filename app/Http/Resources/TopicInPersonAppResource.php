<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use App\Models\Meeting;
use Illuminate\Http\Resources\Json\JsonResource;

class TopicInPersonAppResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
   
    public function toArray($request)
    {
        $user = $request->user;
        $format_day =  Carbon::parse($this->modality_in_person_properties->start_date)->format('l, j \d\e F');
        $start_datetime = Carbon::parse($this->modality_in_person_properties->start_date.' '.$this->modality_in_person_properties->start_time);
        $finish_datetime = Carbon::parse($this->modality_in_person_properties->start_date.' '.$this->modality_in_person_properties->finish_time);
        
        $diff_in_minutes = $start_datetime->diffInMinutes($finish_datetime);
        if($diff_in_minutes < 1440){
            $duration = $start_datetime->format('h:i a').' a '.$finish_datetime->format('h:i a');
            $duration = $diff_in_minutes > 60 
                        ? $duration .' ('.round($diff_in_minutes / 60,1).' horas)'
                        : $duration .' ('.$diff_in_minutes.' minutos)';
        }else{
            $duration = $start_datetime->format('h:i a').' a '.$finish_datetime->format('h:i a');
            $duration = $duration .' ('.round($diff_in_minutes / 1440,1).' días)';
            $format_day =  Carbon::parse($this->modality_in_person_properties->start_date)->format('l, j \d\e F');
        }
        $date_init = $start_datetime->format('Y-m-d H:i') ;
        $is_today = $start_datetime->isToday();
        $is_ontime = now()->between($start_datetime, $finish_datetime);
        $modality_code = $this->course->modality->code;
        $is_host = $user->id == $this->modality_in_person_properties->host_id;
        if(isset($this->modality_in_person_properties->cohost_id ) && $user->id == $this->modality_in_person_properties->cohost_id){
            $is_host = true;
        }
        $session_data = [
            'key' => $this->modality_in_person_properties->start_date,
            'image' => get_media_url($this->course->imagen,'s3'),
            'course_name' => $this->course->name,
            'name' => $this->name,
            'session_code'=> $modality_code,
            'description_code' => 'Cursos de la empresa realizadas por un expositor.',
            'course_id' => $this->course_id,
            'topic_id' => $this->id,
            'reference' => $this->modality_in_person_properties->reference,
            'location'=> $this->modality_in_person_properties->ubicacion,
            'show_medias_since_start_course'=>  $this->modality_in_person_properties->show_medias_since_start_course,
            'is_host' => $is_host,
            'duration' => $duration,
            'url_maps' => isset($this->modality_in_person_properties?->url) ? $this->modality_in_person_properties?->url : '',
            'is_today'=> $is_today,
            'is_ontime'=> $is_ontime,
            'date_init' => $date_init,
            'format_day' => fechaCastellanoV2($format_day),
            'required_signature'=>$this->course->modality_in_person_properties->required_signature,
            'school_id' => $this->course->schools->first()?->id
        ];
        if($modality_code == 'virtual'){
            $meeting = Meeting::where('model_type','App\\Models\\Topic')->where('model_id',$this->id)->first();
            if($meeting){
                $session_data['zoom'] = MeetingAppResource::collection([$meeting]);
            }
        }
        return $session_data;
    }
}
