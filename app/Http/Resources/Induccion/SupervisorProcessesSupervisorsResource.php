<?php

namespace App\Http\Resources\Induccion;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class SupervisorProcessesSupervisorsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'fullname' => $this->fullname,
            'document' => $this->document ?? 'Sin documento',
            'module' => $this->resource->subworkspace?->name ?? 'No module',
            'active' => !!$this->active,
        ];
    }
}
