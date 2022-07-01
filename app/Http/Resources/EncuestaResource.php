<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EncuestaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $sections = [ 'xcurso'=> 'Cursos', 'libre'=> 'Libre' ];

        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'image' => space_url($this->imagen),
            'active' => $this->estado ? true : false,
            'anonima' => $this->anonima == 'si' ? 'Anónima' : 'No anónima',
            'tipo' => $sections[$this->tipo] ?? '',

            'preguntas_count' => $this->preguntas_count,
            'encuestas_preguntas_route' => route('encuestas_preguntas.list', $this->id),

            'created_at' => $this->created_at->format('d/m/Y g:i a'),
            'updated_at' => $this->updated_at->format('d/m/Y g:i a'),

            // 'escuelas_count' => $this->categorias_count,
            // 'usuarios_count' => thousandsFormat($this->usuarios_count),

        ];
    }
}
