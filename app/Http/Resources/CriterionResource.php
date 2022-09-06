<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CriterionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $criterion = $this;

        return [
            'id' => $criterion->id,
            'name' => $criterion->name,
            'data_type' => $criterion->field_type?->name,

            'position' => $criterion->position,

            'values_count' => $criterion->values_count,
            'values_route' => route('criterion_values.list', $criterion->id),
            'values_route_wk' => route('criterion_values_wk.list', $criterion->id),

            'created_at' => $criterion->created_at ? $criterion->created_at->format('d/m/Y g:i a') : 'No definido',
            'updated_at' => $criterion->created_at ? $criterion->updated_at->format('d/m/Y g:i a') : 'No definido',
        ];
    }
}
