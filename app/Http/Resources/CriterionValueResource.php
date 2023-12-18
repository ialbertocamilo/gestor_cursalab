<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CriterionValueResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $criterion_value = $this;
        // info($criterion_value->id);
        $column_name = $criterion_value->getCriterionValueColumnName();

        // info($column_name);

        return [
            'id' => $criterion_value->id,
            'name' => $criterion_value->$column_name ?? '--',
            // 'name' => $criterion_value->$column_name ?? (int) $criterion_value->value_text,
//            'valor' => $criterion_value->valor,
//            'type' => $criterion_value->tipo_criterio,
//            'image' => $criterion_value->config ? space_url($criterion_value->config->logo) : '',
//            // 'active' => $criterion_value->estado ? true : false,
//
           'users_count' => $criterion_value->users_count ?? 0,

            'created_at' => $criterion_value->created_at ? $criterion_value->created_at->format('d/m/Y g:i a') : 'No definido',
            'updated_at' => $criterion_value->created_at ? $criterion_value->updated_at->format('d/m/Y g:i a') : 'No definido',
        ];
    }
}
