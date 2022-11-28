<?php

namespace App\Http\Resources\Curso;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\FileService;

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
        if (is_null($request->school_id)) {
            $route_edit = route('curso.editCurso', [$this->id]);
            $route_topics = route('tema.list', [$this->id]);
        } else {
            $route_edit = route('cursos.editCurso', [$request->school_id, $this->id]);
            $route_topics = route('temas.list', [$request->school_id, $this->id]);
        }

        $schools = $this->schools->pluck('name')->toArray();

        info($this->compatibilities_count);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'orden' => $this->position,
            // 'position' => $this->position,
            'nombre' => $this->name,
            'schools' => implode(',', $schools),
            'image' => FileService::generateUrl($this->imagen),
            // 'medium_image' => FileService::generateUrl($this->imagen),
            'temas_count' => $this->topics_count,
            'encuesta_count' => $this->polls_count,
            'segments_count' => $this->segments_count,
            'active' => $this->active,
            'config_id' => '',

            'custom_curso_nombre' => '',

            'actualizaciones' => '',

            'edit_route' => $route_edit,
            'temas_route' => $route_topics,

            'compatibilities_count' => $this->compatibilities_count,
            'compatibility_available' => true,
        ];
    }
}
