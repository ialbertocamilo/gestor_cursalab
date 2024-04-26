<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChecklistSearchResource extends JsonResource
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
            'id'=>$this->id,
            'title'=>$this->title,
            'active' => $this->active,
            'status' =>$this->active,
            'modality' => $this->modality,
            'checklist_modality' => $this->modality->alias,
            'replicate' => $this->replicate ? 'Sí' : 'No',
            'type' => $this->type,
            'checklist_type' => $this->type->name,
            'finishes_at' => $this->finishes_at ?? 'Sin vigencia',
            'activities_count' => $this->activities_count,
            'segments_count' => $this->segments_count,
            'supervisors_count' => ( count($this->supervisor_criteria) > 0 || count($this->supervisor_ids)) ? 1 : 0,
            'can_create_segmentation' => $this->type->code != 'curso'
        ];
    }
}
