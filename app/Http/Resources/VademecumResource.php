<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VademecumResource extends JsonResource
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
            'nombre' => clean_html($this->nombre, 50),
            'modulos' => $this->getModules(),
            'images' => $this->getModulesImages(),
            'active' => $this->estado ? true : false,

            'categoria_id' => $this->categoria->nombre ?? 'No definido',
            'subcategoria_id' => $this->subcategoria->nombre ?? 'No definido',

            'scorm_route' => $this->media->file ?? '',

            'created_at' => $this->created_at->format('d/m/Y g:i a'),
            'updated_at' => $this->updated_at->format('d/m/Y g:i a'),
        ];
    }

    public function getModulesImages()
    {
        $data = [];

        foreach($this->modulos AS $modulo)
        {
            $data[] = ['name' => $modulo->etapa, 'image' => space_url($modulo->logo)];
        }

        return $data;
    }

    public function getModules()
    {
        $modulos = $this->modulos->pluck('etapa')->toArray();

        return implode(', ', $modulos);
    }
}
