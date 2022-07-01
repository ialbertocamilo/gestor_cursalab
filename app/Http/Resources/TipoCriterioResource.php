<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TipoCriterioResource extends JsonResource
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
            'nombre_plural' => $this->nombre_plural,
            'data_type' => $this->data_type,
            // 'image' => space_url($this->imagen),
            // 'active' => $this->estado ? true : false,

            'orden' => $this->orden,
            'criterios_count' => $this->criterios_count,
            // 'publication_date' => $this->getPublicationDate(),
            // 'body' => clean_html($this->content, 30),

            'criterios_route' => route('criterio.list', $this->id),

            'created_at' => $this->created_at ? $this->created_at->format('d/m/Y g:i a') : 'No definido',
            'updated_at' => $this->created_at ? $this->updated_at->format('d/m/Y g:i a') : 'No definido',
        ];
    }
}
