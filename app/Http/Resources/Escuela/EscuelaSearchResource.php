<?php

namespace App\Http\Resources\Escuela;

use Illuminate\Http\Resources\Json\JsonResource;

class EscuelaSearchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $modalidades = config( 'constantes.modalidad' );

        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'image' => space_url($this->imagen),
            'active' => $this->estado,
            'orden' => $this->orden,

            'modalidad' => $modalidades[$this->modalidad] ?? '',

            'edit_route' => route('escuelas.edit', [$this->config_id, $this->id ]),
            'cursos_count' => $this->cursos_count,

            'cursos_route' => route('cursos.list', [$this->config_id, $this->id ]),
        ];
    }
}
