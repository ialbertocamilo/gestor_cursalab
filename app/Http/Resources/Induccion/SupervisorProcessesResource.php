<?php

namespace App\Http\Resources\Induccion;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupervisorProcessesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request $request
     * @return array
     */
    public function toArray($request)
    {
         return [
            'id' => $this->id,
            'workspace_id' => $this->workspace_id,
            'title' => $this->title,
            'active' => $this->active ?? false,

            'finishes_at' => $this->finishes_at ? date('d-m-Y', strtotime($this->finishes_at)) : null,
            'starts_at' => $this->starts_at ? date('d-m-Y', strtotime($this->starts_at)) : null,
            'participants' => rand(12,35),
            'percentage' => rand(10,80),
        ];
    }
}
