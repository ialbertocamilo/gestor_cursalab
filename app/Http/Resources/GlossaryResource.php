<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GlossaryResource extends JsonResource
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
            'name' => $this->name,
            'modules' => $this->getCodeModules(),
            'images' => $this->getModulesImages(),
            'active' => !!$this->active,

            'categoria_id' => $this->categoria->nombre ?? '',

            'created_at' => $this->created_at->format('d/m/Y g:i a'),
            'updated_at' => $this->updated_at->format('d/m/Y g:i a'),
        ];
    }

    public function getModulesImages()
    {
        $data = [];

        foreach($this->modules AS $module)
        {
            $data[] = [
                'name' => $module->etapa,
                'image' => space_url($module->logo)
            ];
        }

        return $data;
    }

    public function getCodeModules()
    {
        $text = '';
        $total = count($this->modules);
        $i = 0;

        foreach($this->modules AS $module)
        {
            $text .= '(' . $module->codigo_matricula . ') ' . $module->pivot->codigo;

            if(++$i !== $total)
            {
                $text .= " - ";
            }

        }

        return $text;
    }
}
