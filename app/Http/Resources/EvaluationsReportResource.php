<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EvaluationsReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $school = $this->course->schools->first();

        $subworkspaces_ids = [];
        $school->subworkspaces->each(function($workspace) use(&$subworkspaces_ids) {
            $subworkspaces_ids[] = $workspace->pivot->subworkspace_id;
        });

        $subWorkspaces_names = $request->subworkspaces->whereIn('id', $request->modules)
                                                      ->pluck('name')->implode(', ');

        return [
            'topic_id' => $this->id,
            'subworkspaces_names' => $subWorkspaces_names,
            'school_name' => $school->name,
            'course_name' => $this->course->name,
            'topic_name' => $this->name,
            'total_evaluations' => $this->total_evaluations,
            'total_corrects' => (int) $this->total_corrects ?? 0,
            'total_incorrects' => (int) $this->total_incorrects ?? 0
        ];
    }
}
