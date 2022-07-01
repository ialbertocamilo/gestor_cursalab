<?php

namespace App\Http\Resources\ReporteSupervisor;

use Illuminate\Http\Resources\Json\JsonResource;

class ReporteSupervisorSearchResource extends JsonResource
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
            'modulo' => $this->config->etapa,
            'nombre' => "$this->dni - $this->nombre",
            'area' => $this->reporte_supervisor[0]->valor,
            'area_id' => $this->reporte_supervisor[0]->id,
            'usuario_id' => $this->id
        ];
    }
}
