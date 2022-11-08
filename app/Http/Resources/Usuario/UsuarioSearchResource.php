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
        return [
            'id' => $this->id,
            'nombre' => $this->fullname,
            'name' => $this->fullname,
            'image' => space_url($this->config?->logo) ?? 'No logo',
            'document' => $this->document ?? 'Sin documento',
            'module' => $this->resource->subworkspace->name ?? 'No module',
            'active' => !!$this->active,
            'failed_topics_count' => $this->failed_topics_count,
            'pruebas_desaprobadas' => ($request->superuser AND $this->failed_topics_count) ? true : false,
           // 'pruebas_desaprobadas' => true,
            'reporte_route' => route('exportar.node', ['dni' => $this->dni]),
            // 'carrera' => $this->matricula_presente->carrera->nombre ?? '----',
            // 'ciclo_actual' => $this->matricula_presente->ciclo->nombre ?? '---',
        ];
    }
}
