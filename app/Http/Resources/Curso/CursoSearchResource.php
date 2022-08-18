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
        $route_edit = route('cursos.editCurso', [$this->schools()->first()->id, $this->id]);
        $route_topics = route('temas.list', [$this->schools()->first()->id, $this->id]);
        return [
            'id' => $this->id,
            'name' => $this->name,
            'nombre' => $this->name,
            'image' => space_url($this->imagen),
            'temas_count' => $this->topics_count,
            'encuesta_count' => $this->polls_count,
            'segments_count' => $this->segments_count,
            'active' => $this->active,
            'config_id' => '',

            'custom_curso_nombre' => '',

            'actualizaciones' => '',

            'edit_route' => $route_edit,
            'temas_route' => $route_topics,
        ];
    }
}
