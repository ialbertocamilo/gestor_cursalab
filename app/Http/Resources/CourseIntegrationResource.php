<?php

namespace App\Http\Resources;
use App\Models\Course;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseIntegrationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $schools = $this->schools->map(function($school){
            return [
                'school_code'=>$school->id,
                'school'=>$school->name
            ];
        });
        $user_active_having_course = $this->usersSegmented($this->segments,'');
        return [
            "year_course"=> date('Y',strtotime($this->created_at)),
            "budget"=>$this->investment,
            "course" => $this->name,
            "course_code" => $this->id,
            "effort"=>$this->duration,
            "modality" => ($this->type) ? $this->type->name  : null ,
            "schools" => $schools,
            "total_user_completed"=> $this->summaries_count,
            "total_user_assignment"=>$user_active_having_course
            // "cnt_activities_curso"=> "Cantidad de actividades configuradas para el curso",
            // "date_start"=>"Fecha inicio de curso",
            // "date_due"=>"Fecha de vencimiento de cursos",
        ];
    }
}
