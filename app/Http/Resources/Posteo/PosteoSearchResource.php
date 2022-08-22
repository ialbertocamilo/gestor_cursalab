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
        $topic = $this;

        return [
            'id' => $topic->id,
            'nombre' => $topic->name,
            'tipo_evaluacion' => $topic->evaluation_type->name, //$topic->getTipoEvaluacion(),
            'image' => get_media_url($topic->imagen),
            'active' => (bool)$topic->active,
            'orden' => $topic->position,
            'es_evaluable' => $topic->assessable === 1,
            'preguntas_count' => $topic->questions_count,

            'edit_route' => route('temas.editTema', [$request->school_id, $request->course_id, $topic->id]),
            'evaluacion_route' => route('temas.preguntas_list', [$request->school_id, $request->course_id, $topic->id]),
        ];
    }
}
