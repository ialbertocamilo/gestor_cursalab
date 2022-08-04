<?php

namespace App\Http\Resources\Curso;

use Illuminate\Http\Resources\Json\JsonResource;

class CursoSearchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'nombre' => $this->name,
            'image' => space_url($this->imagen),
            'orden' => $this->position,
            'temas_count' => $this->topics_count,
            'encuesta_count' => $this->poll_count,
            'active' => $this->active,
            'config_id' => '',

            'custom_curso_nombre' => '',

            'actualizaciones' => '',

            'edit_route' => route('cursos.editCurso', [$request->school_id, $this->id]),
            'temas_route' => '', //route('temas.list', [$this->config_id, $this->school_id, $this->id]),
        ];
    }
}
