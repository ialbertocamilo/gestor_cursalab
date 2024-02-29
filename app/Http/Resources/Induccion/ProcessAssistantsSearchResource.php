<?php

namespace App\Http\Resources\Induccion;

use Illuminate\Http\Resources\Json\JsonResource;

class ProcessAssistantsSearchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $count_absences = $this->resource->summary_process()->where('process_id', $this->process)->first()?->absences ?? 0;

        return [
            'id' => $this->id,
            'fullname' => $this->fullname,
            'document' => $this->document ?? 'Sin documento',
            'module' => $this->resource->subworkspace?->name ?? 'No module',
            'active' => !!$this->active,
            'absences' => $count_absences.'/'.$this->limit_absences,
            'percentage' => 0
        ];
    }
}
