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
        $summary_user = $this->summary;
        $summary_courses_user = 
           ($summary_user)
            ? $summary_courses_user = $this->summary_courses()
                // ->with(['course:id,name,type_id','course.type:id,name','course.schools:id,name'])
                ->with(['course:id,name,type_id','course'=>[
                    'type:id,name','schools:id,name'
                ]])

                ->whereHas('course', fn($q) => $q->whereIn('id', $assigned_courses->pluck('id')))
                ->whereRelation('status', 'code', 'aprobado')
                ->select('id','course_id','grade_average','advanced_percentage','passed','certification_issued_at')->get()
            : [];
        $summary_courses_completed = [];
        foreach ($summary_courses_user as $summary){
            $schools = $summary->course->schools->map(function($school){
                return [
                    'school_code'=>$school->id,
                    'school'=>$school->name
                ];
            });
            $summary_courses_completed[] = [
                'schools'=>$schools,
                "course_code" => $summary->course_id,
                "course" => $summary->course->name,
                "modality" => $summary->course->type->name,
                "score" => $summary->grade_average,
                "percentage" => $summary->advanced_percentage,
                "result" => $summary->passed ? 'Aprobado'  : 'Desaprobado',
                "date" => $summary->certification_issued_at
            ];
        }
        
        $q_completed_courses = $summary_user ?
            $summary_courses_user->count()
            : 0;
        
        $general_percentage = $assigned_courses->count() > 0 && $summary_user ? round(($q_completed_courses / $assigned_courses->count()) * 100) : 0;
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
