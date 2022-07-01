<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GlosarioResource extends JsonResource
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
            'module' => $this->getCodeModules(),
            'images' => $this->getModulesImages(),
            'active' => $this->estado ? true : false,

            'categoria_id' => $this->categoria->nombre ?? '',

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

    public function getCodeModules()
    {
        $text = '';
        $total = count($this->modulos);
        $i = 0;

        foreach($this->modulos AS $modulo)
        {
            $text .= '(' . $modulo->codigo_matricula . ') ' . $modulo->pivot->codigo;

            if(++$i !== $total)
            {
                $text .= " - ";
            }

        }

        return $text;
    }
}
