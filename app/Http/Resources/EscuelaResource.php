<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EscuelaResource extends JsonResource
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
            'name' => $this->nombre,
            'image' => space_url($this->imagen),
            'active' => $this->estado ? true : false,
            'orden' => $this->orden,

            'modalidad' => $modalidades[$this->modalidad] ?? '',

            'cursos_count' => $this->cursos_count,

             'cursos_route' => route('modulos.escuelas.cursos', [$this->config_id, $this->id ]),
        ];
    }
}
