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
            'nombre' => $this->nombre,
            'image' => space_url($this->imagen),
            'orden' => $this->orden,
            'temas_count' => $this->temas_count,
            'encuesta_count' => $this->encuesta_count,
            'active' => $this->estado,
            'config_id' => $this->categoria->config_id,

            'custom_curso_nombre' => '',

            'actualizaciones' => $this->getActualizaciones(),

            'edit_route' => route('cursos.editCurso', [$this->config_id, $this->categoria_id, $this->id]),
            'temas_route' => route('temas.list', [$this->config_id, $this->categoria_id, $this->id]),
        ];
    }
}
