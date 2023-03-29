<?php

namespace App\Http\Resources\Escuela;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\FileService;

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

        $modules = $this->subworkspaces->pluck('name')->toArray();

        return [
            'id' => $this->id,
            'nombre' => $this->name,
            'name' => $this->name,
            'image' => FileService::generateUrl($this->imagen),
            'images' => $this->getModulesImages(),
            'modules' => implode(', ', $modules),
            'active' => $this->active,
            'orden' => $this->position,
            'position' => $this->position,

            'modalidad' => $modalidades[$this->modalidad] ?? '',

            'edit_route' =>  route('escuelas.edit', [$this->id]),
            'cursos_count' => $this->courses_count,
            'has_no_courses' => $this->courses_count == 0,
            // 'has_no_courses' => true,

            'created_at' => $this->created_at ? $this->created_at->format('d/m/Y g:i a') : '-',

            'cursos_route' => route('cursos.list', [$this->id]),
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
