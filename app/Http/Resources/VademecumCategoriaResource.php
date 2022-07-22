<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VademecumCategoriaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request $request
     * @return array
     */
    public function toArray($request)
    {
         return [
            'id' => $this->id,
            'nombre' => clean_html($this->name, 100),
            'active' => $this->active ? true : false,

            'subcategorias_route' => route('vademecum.categorias.subcategorias.list', $this->id),
            'subcategorias_count' => $this->child_count,

            'created_at' => $this->created_at->format('d/m/Y g:i a'),
            'updated_at' => $this->updated_at->format('d/m/Y g:i a'),
        ];
    }
}
