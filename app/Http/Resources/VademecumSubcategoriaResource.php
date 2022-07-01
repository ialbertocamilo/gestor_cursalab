<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VademecumSubcategoriaResource extends JsonResource
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
            'nombre' => clean_html($this->nombre, 100),
            'active' => $this->estado ? true : false,

            'created_at' => $this->created_at->format('d/m/Y g:i a'),
            'updated_at' => $this->updated_at->format('d/m/Y g:i a'),
        ];
    }
}
