<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ModuloResource extends JsonResource
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
            'name' => $this->etapa,
            'image' => space_url($this->logo),
            'active' => $this->estado,

            'escuelas_count' => (string)$this->categorias_count,
            'usuarios_count' => (string)thousandsFormat($this->usuarios_count),
            'carreras_count' => (string)thousandsFormat($this->carreras_count),

            'escuelas_route' => route('escuelas.list', $this->id),
            'usuarios_route' => route('usuarios.list', ['modulo' => $this->id]),
            'carreras_route' => route('carreras.index'),
        ];
    }
}
