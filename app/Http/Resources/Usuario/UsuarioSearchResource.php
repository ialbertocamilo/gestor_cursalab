<?php

namespace App\Http\Resources\Usuario;

use Illuminate\Http\Resources\Json\JsonResource;

class UsuarioSearchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // info($this->criterion_values);

        return [
            'id' => $this->id,
            'nombre' => $this->fullname,
            'name' => $this->fullname,
            'image' => space_url($this->config?->logo) ?? 'No logo',
            'document' => $this->document ?? 'Sin documento',
            'module' => $this->resource->subworkspace->name ?? 'No module',
            'active' => !!$this->active,
            'failed_topics_count' => $this->failed_topics_count,
            'pruebas_desaprobadas' => $this->failed_topics_count ? true : false,
            // 'pruebas_desaprobadas' => ($request->superuser AND $this->failed_topics_count) ? true : false,
           // 'pruebas_desaprobadas' => true,
            'reporte_route' => route('exportar.node', ['dni' => $this->document]),
            'is_super_user'=>auth()->user()->isAn('super-user'),

            'career' => $this->criterion_values->where('criterion_id', 41)->first()->value_text ?? '----',
            'cycle' => $this->criterion_values->where('criterion_id', 40)->sortBy('position')->last()->value_text ?? '---',
        ];
    }
}
