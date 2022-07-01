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
            'nombre' => $this->nombre,
            'tipo_evaluacion' => $this->getTipoEvaluacion(),
            'image' => space_url($this->imagen),
            'active' => (bool)$this->estado,
            'orden' => $this->orden,
            'es_evaluable' => $this->evaluable === 'si',
            'preguntas_count' => $this->preguntas_count,

            'edit_route' => route('temas.editTema', [$this->categoria->config_id, $this->categoria_id, $this->curso_id, $this->id]),
            'evaluacion_route' => route('temas.preguntas_list', [$this->categoria->config_id, $this->categoria_id, $this->curso_id, $this->id]),
        ];
    }
}
