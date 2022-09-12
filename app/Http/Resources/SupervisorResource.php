<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SupervisorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $supervisor = $this;

        return [
            'id' => $supervisor->id,
            'nombre' => $supervisor->fullname,
//            'apellidos' => $user_relationship->apellido_paterno . ' ' . $user_relationship->apellido_materno,
            'dni' => $supervisor->document,
            'modulo' => $supervisor->subworkspace->name,
            'users_count' => $supervisor->users_count,
            'segments_count' => $supervisor->segments_count,
            'active' => 1
        ];
    }
}
