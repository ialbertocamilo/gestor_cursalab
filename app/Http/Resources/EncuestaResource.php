<?php

namespace App\Http\Resources;

use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EncuestaResource extends JsonResource
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
            'titulo' => $this->titulo,
            'image' => FileService::generateUrl($this->imagen),
            'active' => $this->active,
            'anonima' => $this->anonima ? 'Anónima' : 'No anónima',
            // 'tipo' => $sections[$this->tipo] ?? '',
            'tipo' => $this->type->name ?? '',

            'preguntas_count' => $this->preguntas_count,
            'encuestas_preguntas_route' => route('encuestas_preguntas.list', $this->id),

            'created_at' => $this->created_at->format('d/m/Y g:i a'),
            'updated_at' => $this->updated_at->format('d/m/Y g:i a'),

            // 'escuelas_count' => $this->categorias_count,
            // 'usuarios_count' => thousandsFormat($this->usuarios_count),

        ];
    }
}
