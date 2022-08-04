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

        foreach ($this->block_children as $row) {
            foreach ($row->child->segments as $key => $segment) {
                $grouped = $segment->values->groupBy('criterion_id');

                $new = [];

                foreach ($grouped as $group) {
                    
                    $criterion = $group->first()->criterion;

                    $criterion->_values = $group->toArray();

                    $new[] = $criterion;
                }

                $segment->_criteria = $new; 
            }
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'open' => false,
            // 'workspace_session' => session('workspace') , // DEV
            // 'custom_meeting_name' => $this->name,

            'segments_count' => $this->segments_count,
            'segments' => $this->segments,

            'children_count' => $this->children_count,
            // 'children' => $this->children,
            'block_children' => $this->block_children,
            // 'criteria_count' => $this->criterion_values->groupBy('criterion')->count(),

            'created_at' => $this->created_at ? $this->created_at->format('d/m/Y g:i a') : 'No definido',
            'updated_at' => $this->updated_at ? $this->updated_at->format('d/m/Y g:i a') : 'No definido',
        ];
    }
}
