<?php

namespace App\Http\Resources\Posteo;

use Illuminate\Http\Resources\Json\JsonResource;

class PosteoSearchResource extends JsonResource
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
            'nombre' => $this->name,
            'tipo_evaluacion' => '', //$this->getTipoEvaluacion(),
            'image' => space_url($this->imagen),
            'active' => (bool)$this->active,
            'orden' => $this->position,
            'es_evaluable' => $this->assessable === 1,
            'preguntas_count' => $this->questions_count,

            'edit_route' => route('temas.editTema', [$request->school_id, $request->course_id, $this->id]),
            'evaluacion_route' => route('temas.preguntas_list', [$request->school_id, $request->course_id, $this->id]),
        ];
    }
}
