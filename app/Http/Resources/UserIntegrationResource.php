<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class UserIntegrationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $assigned_courses =  $this->getCurrentCourses();
 
        $summary_courses_completed = collect();
        foreach ($this->summary_courses as $summary){
            $schools = [];
            if($summary->course){
                $schools = $summary->course->schools->map(function($school){
                    return [
                        'school_code'=>$school->id,
                        'school'=>$school->name
                    ];
                });
            }
            $summary_courses_completed->push([
                'schools'=>$schools,
                "course_code" => $summary->course_id,
                "course" => isset($summary->course) ? $summary->course->name : '-',
                "modality" => isset($summary->course->type) ? $summary->course->type->name : '',
                "score" => $summary->grade_average,
                "percentage" => $summary->advanced_percentage,
                "result" => $summary->status->code,
                "date" => $summary->certification_issued_at
            ]);
        }
        
        $q_completed_courses =$summary_courses_completed->count() > 0 ?
            $summary_courses_completed->where('result','aprobado')->count()
            : 0;
        
        $general_percentage = $assigned_courses->count() > 0 && $this->summary_courses ? round(($q_completed_courses / $assigned_courses->count()) * 100) : 0;
        $general_percentage = min($general_percentage, 100);

        
        return [
            'workspace'=>$this->subworkspace->parent->name,
            'module'=>$this->subworkspace->name,
            'username'=>$this->username,
            'fullname'=>$this->fullname,
            'total_assigned' => $assigned_courses->count(),
            'total_completed'=>$q_completed_courses,
            'total_percentage'=>$general_percentage,
            'completed_courses'=>$summary_courses_completed
        ];
    }
}
