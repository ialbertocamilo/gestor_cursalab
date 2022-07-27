<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BlockResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name , // DEV
            'open' => false , // DEV
            // 'custom_meeting_name' => $this->name,
            // 'type' => $this->type->name,
            // 'host' => $this->host->nombre ?? 'No definido',

            'criterion_values_count' => $this->criterion_values_count,
            'criterion_values' => $this->criterion_values,
            'segments_count' => $this->segments_count,
            'segments' => $this->segments,
            'block_segments' => $this->block_segments,
            'criteria_count' => $this->criterion_values->groupBy('criterion')->count(),

            // 'editable' => $this->canBeEdited(),
            // 'cancelable' => $this->canBeCancelled(),
            // 'deletable' => $this->canBeDeleted(),
            // 'is_live' => $this->isLive(),

            'created_at' => $this->created_at ? $this->created_at->format('d/m/Y g:i a') : 'No definido',
            'start' => $this->created_at ? $this->created_at->format('d/m/Y g:i a') : 'No definido',
            'updated_at' => $this->updated_at ? $this->updated_at->format('d/m/Y g:i a') : 'No definido',
            'end' => $this->updated_at ? $this->updated_at->format('d/m/Y g:i a') : 'No definido',
        ];
    }
}
