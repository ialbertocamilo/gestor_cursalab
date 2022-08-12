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
        $column_name = $criterion_value->getCriterionValueColumnName();

        return [
            'id' => $criterion_value->id,
            'name' => $criterion_value->$column_name,
//            'valor' => $criterion_value->valor,
//            'type' => $criterion_value->tipo_criterio,
//            'image' => $criterion_value->config ? space_url($criterion_value->config->logo) : '',
//            // 'active' => $criterion_value->estado ? true : false,
//
//            'usuarios_count' => $criterion_value->usuarios_count,

            'created_at' => $criterion_value->created_at ? $criterion_value->created_at->format('d/m/Y g:i a') : 'No definido',
            'updated_at' => $criterion_value->created_at ? $criterion_value->updated_at->format('d/m/Y g:i a') : 'No definido',
        ];
    }
}
