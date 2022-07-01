<?php

namespace App\Http\Resources\Entrenador;

use Illuminate\Http\Resources\Json\JsonResource;

class EntrenadorSearchResource extends JsonResource
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
            'name' => $this->nombre,
            'dni' => $this->dni,
//            'count_active_students' => $this->alumnos_activos->count()
        ];
    }
}
