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
        $user_relationship = $this;
        return [
            'id' => $user_relationship->user->id,
            'nombre' => $user_relationship->user->fullname,
//            'apellidos' => $user_relationship->apellido_paterno . ' ' . $user_relationship->apellido_materno,
            'dni' => $user_relationship->user->document,
            'modulo' => $user_relationship->user->subworkspace->name,
//            'usuarios_count' => $user_relationship->usuarios_count,
//            'criterios_count' => $user_relationship->criterios_count,
            'active' => 1
        ];
    }
}
