<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EvaluationDetailReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'question_id'=> $this['id'],
            'subworkspaces_names' => $request->subworkspaces_names,
            'school_name' => $request->school_name,
            'course_name' => $request->course_name,
            'topic_name' => $request->topic_name,
            'question_name' => $this['pregunta'],
            'total_evaluations' => $this['results']['total_evaluations'],
            'total_corrects' => $this['results']['total_corrects'],
            'total_incorrects' => $this['results']['total_incorrects']
        ];
    }
}
