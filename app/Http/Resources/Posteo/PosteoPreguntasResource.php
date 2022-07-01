<?php

namespace App\Http\Resources\Posteo;

use Illuminate\Http\Resources\Json\JsonResource;

class PosteoPreguntasResource extends JsonResource
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
            'custom_tema_preguntas_pregunta' => clean_html($this->pregunta),
            'active' => (bool)$this->estado,
            'tipo_pregunta' => ucfirst($this->tipo_pregunta),
        ];
    }
}
