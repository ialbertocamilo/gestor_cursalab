<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CourseProgressIntegrationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {   
        $status     = isset($this->summary_courses[0]->status) ? $this->summary_courses[0]->status->name : 'Pendiente';
        $date_start = isset($this->summary_courses[0]->created_at) ? date('Y-m-d',strtotime($this->summary_courses[0]->created_at)) : null ;
        return [
            'course_code'   => $request->course->id,
            'course_name'   => $request->course->name,
            'document'      => $this->document,
            'status_course' => $status,
            'date_start'    => $date_start
        ];
    }
}
