<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BoticaResource extends JsonResource
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
            'nombre' => $this->nombre,
            'criterio_id' => $this->criterio->valor ?? 'No definido',
            // 'grupo_nombre' => $this->criterio->valor ?? 'No definido',
            // 'grupo_nombre' => $this->grupo_nombre,
            'codigo_local' => $this->codigo_local,
            'image' => $this->config ? space_url($this->config->logo) : '',

            'usuarios_count' => $this->usuarios_count,

            'created_at' => $this->created_at ? $this->created_at->format('d/m/Y g:i a') : 'No definido',
            'updated_at' => $this->created_at ? $this->updated_at->format('d/m/Y g:i a') : 'No definido',
        ];
    }
}
