<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PreguntaFrecuenteResource extends JsonResource
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

            'pregunta' => clean_html($this->pregunta, 40),
            'respuesta' => clean_html($this->respuesta, 50),

            'orden' => $this->orden,

            'active' => $this->estado ? true : false,

            'created_at' => $this->created_at ? $this->created_at->format('d/m/Y g:i a') : 'No definido',
            'updated_at' => $this->created_at ? $this->updated_at->format('d/m/Y g:i a') : 'No definido',
        ];
    }
}
