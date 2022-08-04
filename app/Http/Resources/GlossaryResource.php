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
            'module' => $this->getCodeModules(),
            'images' => $this->getModulesImages(),
            'active' => !!$this->active,

            'categoria_id' => $this->categoria->name ?? '',

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
                'name' => $module->value_text,
                'image' => space_url($module->logo)
            ];
        }

        return $data;
    }

    public function getCodeModules()
    {
        $codes = [];
        $glossaryModules = $this->glossary_module->toArray();

        if (count($glossaryModules) > 0) {

            foreach ($glossaryModules as $module) {
                $codes[] = $module['code'];
            }

            return '(' . implode('-', $codes) . ')';

        } else {
            return '';
        }
    }
}
