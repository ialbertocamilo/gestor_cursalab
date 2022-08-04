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
        $modalidades = config('constantes.modalidad');

        return [
            'id' => $this->id,
            'nombre' => $this->name,
            'image' => space_url($this->imagen),
            'active' => $this->active,
            'orden' => $this->position,

            'modalidad' => $modalidades[$this->modalidad] ?? '',

            'edit_route' =>  route('escuelas.edit', [$this->id]),
            'cursos_count' => $this->courses_count,

            'cursos_route' => route('cursos.list', [$this->id]),
        ];
    }
}
