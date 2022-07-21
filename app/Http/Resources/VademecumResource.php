<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VademecumResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => clean_html($this->name, 50),
            'modules' => $this->getModules(),
            'images' => $this->getModulesImages(),
            'active' => $this->active ? true : false,

            'category_id' => $this->category->name ?? 'No definido',
            'subcategory_id' => $this->subcategory->name ?? 'No definido',

            'scorm_route' => $this->media->file ?? '',

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

    /**
     * Generate a string with modules names separated by commas
     *
     * @return string
     */
    public function getModules()
    {
        $modules = $this->modules->pluck('value_text')->toArray();
        return implode(', ', $modules);
    }
}
