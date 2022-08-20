<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

use App\Services\FileService;

class SubWorkspaceResource extends JsonResource
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
//            'name' => $this->etapa,
            'name' => $this->name,
            'image' => space_url($this->logo),
            'active' => $this->active,

            // 'escuelas_count' => (string)$this->categorias_count,
            'users_count' => (string)thousandsFormat($this->users_count),
            // 'carreras_count' => (string)thousandsFormat($this->carreras_count),

            // 'escuelas_route' => route('escuelas.list', $this->id),
            'users_route' => route('usuarios.list', ['subworkspace_id' => $this->id]),
            // 'carreras_route' => route('carreras.index'),
        ];
    }
}
