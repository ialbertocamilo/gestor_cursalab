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
        $platform = session('platform');
        // $show_segmentation = !boolval(!$platform || $platform == 'induccion');
        $show_segmentation = false;
        return [
            'id' => $supervisor->id,
            'nombre' => $supervisor->fullname,
            'show_segmentation' => !$show_segmentation,
//            'apellidos' => $user_relationship->apellido_paterno . ' ' . $user_relationship->apellido_materno,
            'dni' => $supervisor->document,
            'modulo' => $supervisor->subworkspace->name,
            'segments_count' => $supervisor->segments_count,
            'active' => 1
        ];
    }
}
