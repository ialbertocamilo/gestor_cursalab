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
        $modalidades = config('constantes.modalidad');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'escuela_nombre' => [
                'name' => $this->name,
            ],
            'image' => space_url($this->imagen),
            'images' => $this->getModulesImages(),
            'active' => $this->active ? true : false,
            'orden' => $this->position,

            'modalidad' => $modalidades[$this->modalidad] ?? '',

            'cursos_count' => $this->courses_count,

            'cursos_route' => route('modulos.escuelas.cursos', [$this->config_id, $this->id]),
        ];
    }

    public function getModulesImages()
    {
        $data = [];

        foreach($this->subworkspaces AS $module)
        {
            $data[] = [
                'name' => $module->name,
                'image' => space_url($module->logo)
            ];
        }

        return $data;
    }
}
