<?php

namespace App\Http\Resources;

use App\Models\ChecklistSupervisor;
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
        $checklistSupervisor = ChecklistSupervisor::where('id',$this->id)->first();
        $route_activities = '/entrenamiento/checklist/v2/'.$this->id.'/activities';
        // $route_activities = route('entrenamiento.checklist.activities', ['checklist' => $this->id]);
        return [
            'id'=>$this->id,
            'title'=>$this->title,
            'active' => $this->active,
            'status' =>$this->active,
            'modality' => $this->modality,
            'checklist_modality' => $this->modality?->alias,
            'replicate' => $this->replicate=='true' ? 'Sí' : 'No',
            'type' => $this->type,
            'checklist_type' => $this->type->name,
            'finishes_at' => $this->finishes_at ?? 'Sin vigencia',
            'activities_count' => $this->activities_count,
            'activities_route' => $route_activities,
            'segments_count' => $this->segments_count,
            'supervisors_count' => $checklistSupervisor->segments($this->id)->first() ? 1 : 0,
            'can_create_segmentation' => $this->type->code != 'curso'
        ];
    }
}
